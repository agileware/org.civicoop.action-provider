<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Relationship;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;
use Dompdf\Exception;

class CreateRelationship extends AbstractAction {

  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Create relationship');
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('relationship_type_id', 'Integer', E::ts('Relationship type'), true, null, 'RelationshipType', null, False),
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
      new Specification('contact_id_a', 'Integer', E::ts('Contact ID A'), true, null, null, null, FALSE),
      new Specification('contact_id_b', 'Integer', E::ts('Contact ID B'), true, null, null, null, FALSE),
    ));

    $customGroups = civicrm_api3('CustomGroup', 'get', array('extends' => 'Relationship', 'is_active' => 1, 'options' => array('limit' => 0)));
    foreach($customGroups['values'] as $customGroup) {
      $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroup['id'], 'is_active' => 1, 'options' => array('limit' => 0)));
      foreach($customFields['values'] as $customField) {
        $spec = CustomField::getSpecFromCustomField($customField, $customGroup['title'].': ', false);
        if ($spec) {
          $specs->addSpecification($spec);
        }
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
    $relationshipParams['relationship_type_id'] = $this->configuration->getParameter('relationship_type_id');
    $relationshipParams['is_active'] = '1';
    if ($this->configuration->getParameter('set_start_date')) {
      $today = new \DateTime();
      $relationshipParams['start_date'] = $today->format('Ymd');
    }

    foreach($this->getParameterSpecification() as $spec) {
      if (stripos($spec->getName(), 'custom_')!==0) {
        continue;
      }
      if ($parameters->doesParameterExists($spec->getName())) {
        $relationshipParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
      }
    }

    try {
      // Do not use api as the api checks for an existing relationship.
      $relationship = \CRM_Contact_BAO_Relationship::add($relationshipParams);
      $output->setParameter('id', $relationship->id);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG
    );
  }

}