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

use Civi\ActionProvider\Utils\Fields;
use CRM_ActionProvider_ExtensionUtil as E;

class GetRelationshipByContactId extends AbstractAction {

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
      new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, False),
      new Specification('contact_id_side', 'String', E::ts('Contact is'), true, null, null, ['a' => E::ts('Contact A'), 'b' => E::ts('Contact B')], False),
      new Specification('inactive', 'Boolean', E::ts('Also return inactive relationships'), false, 0, null, null, false)
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
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
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true, null, null, null, FALSE),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    Fields::getFieldsForEntity($bag,'Relationship', 'get', array());
    return $bag;
  }

  /**
   * Find existing relationship
   *
   * @param $contact_id_a
   * @param $contact_id_b
   * @param $type_id
   * @param bool $also_inactive
   *
   * @return array|false
   */
  protected function findExistingRelationshipId($side, $contact_id, $type_id, $also_inactive=false) {
    $relationshipFindParams = array();
    if ($side == 'a') {
      $relationshipFindParams['contact_id_a'] = $contact_id;
    } else {
      $relationshipFindParams['contact_id_b'] = $contact_id;
    }
    $relationshipFindParams['relationship_type_id'] = $type_id;
    $relationshipFindParams['is_active'] = '1';
    $relationshipFindParams['options']['limit'] = 1;
    try {
      $relationship = civicrm_api3('Relationship', 'getsingle', $relationshipFindParams);
      return $relationship;
    } catch (\Exception $e) {
      // Do nothing
    }
    if ($also_inactive) {
      $relationshipFindParams = array();
      if ($side == 'a') {
        $relationshipFindParams['contact_id_a'] = $contact_id;
      } else {
        $relationshipFindParams['contact_id_b'] = $contact_id;
      }
      $relationshipFindParams['relationship_type_id'] = $type_id;
      $relationshipFindParams['is_active'] = '0';
      $relationshipFindParams['options']['limit'] = 1;
      try {
        $relationship = civicrm_api3('Relationship', 'getsingle', $relationshipFindParams);
        return $relationship;
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
    $inactiveOnes = false;
    if ($this->configuration->doesParameterExists('inactive') && $this->configuration->getParameter('inactive')) {
      $inactiveOnes = true;
    }
    $side = $this->configuration->getParameter('contact_id_side');
    $relationshipTypeId = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
    $relationship = $this->findExistingRelationshipId($side, $parameters->getParameter('contact_id'), $relationshipTypeId, $inactiveOnes);
    if ($relationship) {
      foreach($relationship as $field => $value) {
        if (stripos($field, 'custom_') !== 0) {
          $output->setParameter($field, $value);
        } else {
          $custom_id = substr($field, 7);
          if (is_numeric($custom_id)) {
            $fieldName = CustomField::getCustomFieldName($custom_id);
            if (is_array($value)) {
              // The keys of the array contains the values of the selected options.
              $value = array_keys($value);
            }
            $output->setParameter($fieldName, $value);
          }
        }
      }
    }
  }

}
