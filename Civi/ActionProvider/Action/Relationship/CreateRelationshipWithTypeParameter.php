<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Relationship;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationGroup;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateRelationshipWithTypeParameter extends AbstractAction {

  protected $relationshipTypes = array();
  protected $relationshipTypeIds = array();

  public function __construct() {
    parent::__construct();
    $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $this->relationshipTypes = array();
    $this->relationshipTypeIds = array();
    foreach($relationshipTypesApi['values'] as $relType) {
      $this->relationshipTypes[$relType['name_a_b']] = $relType['label_a_b'];
      $this->relationshipTypeIds[$relType['name_a_b']] = $relType['id'];
    }

  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('set_start_date', 'Boolean', E::ts('Set start date?'), false, 0, null, null, FALSE),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      /**
       * The parameters given to the Specification object are:
       * @param string $name
       * @param string $dataType
       * @param string $title
       * @param bool $required
       * @param mixed $defaultValue
       * @param string|null $fkEntity
       * @param array $options
       * @param bool $multiple
       */
      new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, False),
      new Specification('contact_id_a', 'Integer', E::ts('Contact ID A'), true, null, null, null, FALSE),
      new Specification('contact_id_b', 'Integer', E::ts('Contact ID B'), true, null, null, null, FALSE),
      new Specification('start_date', 'Date', E::ts('Start date'), false),
      new Specification('end_date', 'Date', E::ts('End date'), false),
      new Specification('description', 'String', E::ts('Description'), false),
      new Specification('case_id', 'Integer', E::ts('Case ID'), false, null, null, null, FALSE),
    ));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Relationship');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('id', 'Integer', E::ts('Relationship record ID')),
    ));
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Get the contact and the event.
    $relationshipParams['contact_id_a'] = $parameters->getParameter('contact_id_a');
    $relationshipParams['contact_id_b'] = $parameters->getParameter('contact_id_b');
    $relationshipParams['relationship_type_id'] = $this->relationshipTypeIds[$parameters->getParameter('relationship_type_id')];
    $relationshipParams['is_active'] = '1';
    if ($this->configuration->getParameter('set_start_date')) {
      $today = new \DateTime();
      $relationshipParams['start_date'] = $today->format('Ymd');
    } else {
      if ($parameters->doesParameterExists('start_date')) {
        $relationshipParams['start_date'] = $parameters->getParameter('start_date');
      }
      if ($parameters->doesParameterExists('end_date')) {
        $relationshipParams['end_date'] = $parameters->getParameter('end_date');
      }
    }
    if ($parameters->doesParameterExists('description')) {
      $relationshipParams['description'] = $parameters->getParameter('description');
    }
    if ($parameters->doesParameterExists('case_id')) {
      $relationshipParams['case_id'] = $parameters->getParameter('case_id');
    }

    $relationshipParams['custom'] = array();
    foreach($this->getParameterSpecification() as $spec) {
      if ($spec instanceof SpecificationGroup) {
        foreach($spec->getSpecificationBag() as $subSpec) {
          if (stripos($subSpec->getName(), 'custom_')===0 && $parameters->doesParameterExists($subSpec->getName())) {
            list($customFieldID, $customValueID) = \CRM_Core_BAO_CustomField::getKeyID($subSpec->getApiFieldName(), TRUE);
            $value = $parameters->getParameter($subSpec->getName());
            if (is_array($value)) {
              $value = \CRM_Core_DAO::VALUE_SEPARATOR . implode(\CRM_Core_DAO::VALUE_SEPARATOR, $value) . \CRM_Core_DAO::VALUE_SEPARATOR;
            }
            \CRM_Core_BAO_CustomField::formatCustomField($customFieldID, $relationshipParams['custom'], $value, 'Relationship', $customValueID);
          }
        }
      } elseif (stripos($spec->getName(), 'custom_')===0) {
        if ($parameters->doesParameterExists($spec->getName())) {
          list($customFieldID, $customValueID) = \CRM_Core_BAO_CustomField::getKeyID($spec->getApiFieldName(), TRUE);
          $value = $parameters->getParameter($spec->getName());
          if (is_array($value)) {
            $value = \CRM_Core_DAO::VALUE_SEPARATOR . implode(\CRM_Core_DAO::VALUE_SEPARATOR, $value) . \CRM_Core_DAO::VALUE_SEPARATOR;
          }
          \CRM_Core_BAO_CustomField::formatCustomField($customFieldID, $relationshipParams['custom'], $value, 'Relationship', $customValueID);
        }
      }
    }
    try {
      // Do not use api as the api checks for an existing relationship.
      $relationship = \CRM_Contact_BAO_Relationship::add($relationshipParams);
      $relationship_id = $relationship->id;

      // Update the related memberships
      $contact_ids = [
        'contactTarget' => $relationshipParams['contact_id_b'],
        'contact' => $relationshipParams['contact_id_a'],
      ];
      // When the relationship end date is set to 'null' related memberships are deleted
      if ($relationshipParams['end_date'] == 'null') {
        $relationshipParams['end_date'] = null;
      }
      $action = \CRM_Core_Action::ADD;
      \CRM_Contact_BAO_Relationship::relatedMemberships($relationshipParams['contact_id_a'], $relationshipParams, $contact_ids, $action, TRUE);

      $output->setParameter('id', $relationship_id);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

}
