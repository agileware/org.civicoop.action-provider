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

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Class RegexReplaceValue
 * @package Civi\ActionProvider\Action\Generic
 *
 * Runs a preg_replace call on the input value and stores the result in the output value
 *
 * @see https://www.php.net/manual/en/function.preg-replace.php
 */
class RegexReplaceValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('regex_find', 'String', E::ts('Find (regular expression with delimiters and modifiers)'), true),
      new Specification('regex_replace', 'String', E::ts('Replace (string with group references, e.g. $1)'), true),
      new Specification('replace_count', 'String', E::ts('Replace Count'), true, -1, NULL, [
          -1 => E::ts("all"),
           1 => "1",
           2 => "2",
           3 => "3",
           4 => "4",
           5 => "5",
           6 => "6",
           7 => "7",
           8 => "8",
           9 => "9",
          10 => "10",
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
        new Specification('input_value', 'String', E::ts('Input Value'), true)
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
    $find    = $this->configuration->getParameter('regex_find');
    $replace = $this->configuration->getParameter('regex_replace');
    $count   = $this->configuration->getParameter('replace_count');
    try {
      $value = preg_replace($find, $replace, $value, $count);
    } catch (\Exception $ex) {
      throw new ExecutionException('Invalid Regular Expression');
    }
    $output->setParameter('value', $value);
  }
}
