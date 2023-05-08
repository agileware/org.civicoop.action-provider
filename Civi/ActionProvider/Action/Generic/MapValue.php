<?php
/**
 * @author Björn Endres <endres@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Exception\ExecutionException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\FormProcessor\API\Exception;
use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Class RegexReplaceValue
 * @package Civi\ActionProvider\Action\Generic
 *
 * Runs a preg_replace call on the input value and stores the result in the output value
 *
 * @see https://www.php.net/manual/en/function.preg-replace.php
 */
class MapValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('json_mapping', 'Text', E::ts('Data Mapping (JSON)'), true),
      new Specification('not_found', 'String', E::ts('If not Mapped'), true, -1, NULL, [
          'keep'   => E::ts("leave as is"),
          'delete' => E::ts("empty"),
      ]),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
        new Specification('input_value', 'String', E::ts('Input Value'), true, null, null, null, true)
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('value', 'String', E::ts('Value')),
    ]);
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
    $value   = $parameters->getParameter('input_value');
    $mapping = json_decode($this->configuration->getParameter('json_mapping'), true);
    if ($mapping === NULL) {
      throw new ExecutionException("Couldn't parse mapping JSON");
    }


    if (is_array($value)) {
      $mappedValue = [];
      foreach($value as $v) {
        $mappedValue[] = $this->mapValue($v, $mapping);
      }
    } else {
      $mappedValue = $this->mapValue($value, $mapping);
    }

    $output->setParameter('value', count($mappedValue) > 1 ? $mappedValue : $mappedValue[0]);
  }

  protected function mapValue($value, $mapping) {
    if (isset($mapping[$value])) {
      // value was found => map
      $value = $mapping[$value];
    } else {
      // value as NOT found:
      $not_found_action = $this->configuration->getParameter('not_found');
      switch ($not_found_action) {
        case 'delete':
          $value = '';
          break;

        default:
        case 'keep':
          // nothing to do
          break;
      }
    }
    return $value;
  }

}
