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

class UpdateRelationshipById extends CreateRelationship {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, False),
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
      new Specification('relationship_id', 'Integer', E::ts('Relationship ID'), true, null, null, null, FALSE),
      new Specification('contact_id_a', 'Integer', E::ts('Contact ID A'), false, null, null, null, FALSE),
      new Specification('contact_id_b', 'Integer', E::ts('Contact ID B'), false, null, null, null, FALSE),
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
    $relationship_type_id = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
    $relationship_id = $parameters->getParameter('relationship_id');
    $relationshipParams['id'] = $relationship_id;

    $relationship = civicrm_api3('Relationship', 'getsingle', ['id' => $relationship_id]);

    $relationshipParams['contact_id_a'] = $relationship['contact_id_a'];
    $relationshipParams['contact_id_b'] = $relationship['contact_id_b'];
    if ($parameters->getParameter('contact_id_a')) {
      $relationshipParams['contact_id_a'] = $parameters->getParameter('contact_id_a');
    }
    if ($parameters->getParameter('contact_id_b')) {
      $relationshipParams['contact_id_b'] = $parameters->getParameter('contact_id_b');
    }
    $relationshipParams['relationship_type_id'] = $relationship_type_id;
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
      $action = !empty($relationshipParams['id']) ? \CRM_Core_Action::UPDATE : \CRM_Core_Action::ADD;
      \CRM_Contact_BAO_Relationship::relatedMemberships($relationshipParams['contact_id_a'], $relationshipParams, $contact_ids, $action, TRUE);

      $output->setParameter('id', $relationship_id);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

}
