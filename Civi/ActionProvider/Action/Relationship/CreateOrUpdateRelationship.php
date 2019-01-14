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

class CreateOrUpdateRelationship extends CreateRelationship {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $specs = parent::getConfigurationSpecification();
    $specs->addSpecification(new Specification('also_update_inactive', 'Boolean', E::ts('Update inactive relationships'), false, 0, null, null, false));
    return $specs;
  }

  /**
   * Find existing relationship
   *
   * @param $contact_id_a
   * @param $contact_id_b
   * @param $type_id
   * @param bool $also_inactive
   *
   * @return mixed
   */
  protected function findExistingRelationshipId($contact_id_a, $contact_id_b, $type_id, $also_inactive=false) {
    $relationshipFindParams = array();
    $relationshipFindParams['contact_id_a'] = $contact_id_a;
    $relationshipFindParams['contact_id_b'] = $contact_id_b;
    $relationshipFindParams['relationship_type_id'] = $type_id;
    $relationshipFindParams['is_active'] = '1';
    try {
      $relationship = civicrm_api3('Relationship', 'getsingle', $relationshipFindParams);
      return $relationship['id'];
    } catch (\Exception $e) {
      // Do nothing
    }
    if ($also_inactive) {
      $relationshipFindParams = array();
      $relationshipFindParams['contact_id_a'] = $contact_id_a;
      $relationshipFindParams['contact_id_b'] = $contact_id_b;
      $relationshipFindParams['relationship_type_id'] = $type_id;
      $relationshipFindParams['is_active'] = '0';
      try {
        $relationship = civicrm_api3('Relationship', 'getsingle', $relationshipFindParams);
        return $relationship['id'];
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    return false;
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
    $relationship_id = false;
    $alsoUpdateInactiveOne = false;
    if ($this->configuration->doesParameterExists('also_update_inactive') && $this->configuration->getParameter('also_update_inactive')) {
      $alsoUpdateInactiveOne = true;
    }
    $relationship_id = $this->findExistingRelationshipId($parameters->getParameter('contact_id_a'), $parameters->getParameter('contact_id_b'), $this->relationshipTypes[$parameters->getParameter('relationship_type_id')], $alsoUpdateInactiveOne);
    if ($relationship_id) {
      $relationshipParams['id'] = $relationship_id;
    }
    // Get the contact and the event.
    $relationshipParams['contact_id_a'] = $parameters->getParameter('contact_id_a');
    $relationshipParams['contact_id_b'] = $parameters->getParameter('contact_id_b');
    $relationshipParams['relationship_type_id'] = $this->relationshipTypes[$this->configuration->getParameter('relationship_type_id')];
    $relationshipParams['is_active'] = '1';
    if ($this->configuration->getParameter('set_start_date') && !$relationship_id) {
      $today = new \DateTime();
      $relationshipParams['start_date'] = $today->format('Ymd');
    }
    if ($relationship_id) {
      $relationshipParams['end_date'] = 'null';
    }

    $relationshipParams['custom'] = array();
    foreach($this->getParameterSpecification() as $spec) {
      if (stripos($spec->getName(), 'custom_')!==0) {
        continue;
      }
      if ($parameters->doesParameterExists($spec->getName())) {
        list($customFieldID, $customValueID) = \CRM_Core_BAO_CustomField::getKeyID($spec->getApiFieldName(), TRUE);
        $value = $parameters->getParameter($spec->getName());
        if (is_array($value)) {
          $value = \CRM_Core_DAO::VALUE_SEPARATOR . implode(\CRM_Core_DAO::VALUE_SEPARATOR, $value) . \CRM_Core_DAO::VALUE_SEPARATOR;
        }
        \CRM_Core_BAO_CustomField::formatCustomField($customFieldID, $relationshipParams['custom'], $value, 'Relationship', $customValueID);
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

}