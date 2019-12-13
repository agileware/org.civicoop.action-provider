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
class FormatValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('format','String', E::ts('Format (sprintf)'), true),
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
        new Specification('parameter_1', 'String', E::ts('Input Value 1'), true),
        new Specification('parameter_2', 'String', E::ts('Input Value 2'), false),
        new Specification('parameter_3', 'String', E::ts('Input Value 3'), false),
        new Specification('parameter_4', 'String', E::ts('Input Value 4'), false),
        new Specification('parameter_5', 'String', E::ts('Input Value 5'), false),
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
    $format  = $this->configuration->getParameter('format');
    $param_1 = $parameters->getParameter('parameter_1');
    $param_2 = $parameters->getParameter('parameter_2');
    $param_3 = $parameters->getParameter('parameter_3');
    $param_4 = $parameters->getParameter('parameter_4');
    $param_5 = $parameters->getParameter('parameter_5');

    // run
    $value = sprintf($format, $param_1, $param_2, $param_3, $param_4, $param_5);
    $output->setParameter('value', $value);
  }
}