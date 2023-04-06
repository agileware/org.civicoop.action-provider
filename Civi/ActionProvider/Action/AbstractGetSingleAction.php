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
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    Fields::getFieldsForEntity($bag, $this->getApiEntity(), 'get', $this->getSkippedFields(), $this->getEntityAlias());
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
    }
    catch (\Exception $e) {
      // Do nothing
    }
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    $fieldsToSkip = $this->getSkippedFields();
    $entity = $this->normalizeCustomValues($entity);
    foreach ($entity as $field => $value) {
      if (in_array($field, $fieldsToSkip)) {
        continue;
      }
      if (stripos($field, $this->getEntityAlias() . '_') === 0) {
        $output->setParameter(substr($field, strlen($this->getEntityAlias() . '_')), $value);
      }
      else if (stripos($field, 'custom_') !== 0) {
        $output->setParameter($field, $value);
      }
      else {
        $custom_id = substr($field, 7);
        if (is_numeric($custom_id)) {
          $fieldName = CustomField::getCustomFieldName($custom_id);
          $output->setParameter($fieldName, $value);
        }
      }
    }
  }

  /**
   * This function checks for custom_xx_id and sets it to custom_xx.
   *
   * In some cases the civicrm api returns custom values with a looked up value instead
   * of their ID.
   * In the action provider we dont want to deal with the looked up values.
   * @param $entity
   */
  protected function normalizeCustomValues($entity) {
    $normalizedEntity = array();
    foreach ($entity as $field => $value) {
      if (stripos($field, 'custom_') !== 0) {
        $normalizedEntity[$field] = $value;
        // No a custom field
        continue;
      }
      $custom_id = substr($field, 7);
      if (substr($custom_id, -3) === '_id') {
        $custom_id = substr($custom_id, 0, -3);
        $normalizedEntity['custom_' . $custom_id] = $value;
      }
      elseif (is_numeric($custom_id)) {
        if (isset($normalizedEntity[$field])) {
          continue;
        }
        $normalizedEntity['custom_' . $custom_id] = $value;
      }
    }
    return $normalizedEntity;
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

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getEntityAlias() {
    return strtolower($this->getApiEntity());
  }

}