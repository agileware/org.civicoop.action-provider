<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_Exception;

class OptionValueToLabel extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $optionGroups = array();
    try {
      $option_groups_api = civicrm_api3('OptionGroup', 'get', [
        'options' => [
          'limit' => 0,
          'is_active' => 1,
        ],
      ]);
      foreach($option_groups_api['values'] as $optionGroup) {
        $optionGroups[$optionGroup['name']] = $optionGroup['title'];
      }
    } catch (CRM_Core_Exception $e) {
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
  public function getParameterSpecification(): SpecificationBag {
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
  public function getOutputSpecification(): SpecificationBag {
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
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $value = $parameters->getParameter('value');
    if (!is_array($value)) {
      $value = array($value);
    }
    $option_group_id = $this->configuration->getParameter('option_group_id');

    $labels = array();
    foreach($value as $v) {
      $label = civicrm_api3('OptionValue', 'getvalue', ['return' => 'label', 'value' => $v, 'option_group_id' => $option_group_id,'options' => ['limit' => 1]]);
      $labels[] = $label;
    }

    $output->setParameter('value', count($labels) > 1 ? $labels : $labels[0]);
  }

}
