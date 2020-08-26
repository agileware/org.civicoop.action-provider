<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action;

use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;
use Civi\ActionProvider\Utils\Fields;

/**
 * This is a generic class for action which retrieves data from a single entity.
 * E.g. a single event, participant, contact etc...
 *
 * Class AbstractGetSingleAction
 *
 * @package Civi\ActionProvider\Action
 */
abstract class AbstractGetSingleAction extends AbstractAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  abstract protected function getApiEntity();

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  abstract protected function getIdFromParamaters(ParameterBagInterface $parameters);

  /**
   * @return array
   */
  protected function getSkippedFields() {
    return array();
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
    Fields::getFieldsForEntity($bag,$this->getApiEntity(), 'get', $this->getSkippedFields());
    return $bag;
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    try {
      $id = $this->getIdFromParamaters($parameters);
      $entity = civicrm_api3($this->getApiEntity(), 'getsingle', array('id' => $id));
      if ($entity) {
        $this->setOutputFromEntity($entity, $output);
      }
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    $fieldsToSkip = $this->getSkippedFields();
    foreach($entity as $field => $value) {
      if (in_array($field, $fieldsToSkip)) {
        continue;
      }
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

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

}
