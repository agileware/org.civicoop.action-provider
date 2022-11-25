<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class OptionValueToLabel extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $optionGroups = array();
    $option_groups_api = civicrm_api3('OptionGroup', 'get', array('options' => array('limit' => 0, 'is_active' => 1)));
    foreach($option_groups_api['values'] as $optionGroup) {
      $optionGroups[$optionGroup['name']] = $optionGroup['title'];
    }
    return new SpecificationBag(array(
      new Specification('option_group_id', 'String', E::ts('Option Group'), true, null, null, $optionGroups),
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
      new Specification('value', 'String', E::ts('Value'), true, null, null, null, true),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('value', 'String', E::ts('Value')),
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
    $value = $parameters->getParameter('value');
    $option_group_id = $this->configuration->getParameter('option_group_id');

    $labels = array();
    foreach($value as $v) {
      $label = civicrm_api3('OptionValue', 'getvalue', array('return' => 'label', 'value' => $v, 'option_group_id' => $option_group_id));
      $labels[] = $label;
    }

    $output->setParameter('value', count($labels) > 1 ? $labels : $labels[0]);
  }

}
