<?php
/**
 * @author  Justin Freeman <projects@agileware.com.au>
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
 *
 * @see     https://www.php.net/manual/en/function.preg-replace.php
 * @package Civi\ActionProvider\Action\Generic
 *
 * Runs a preg_replace call on the input value and stores the result in the
 * output value
 *
 */
class RoundNumber extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('round_number_precision', 'Integer', E::ts('Rounding precision'), TRUE, 2),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('round_number', 'String', E::ts('Number value to round'), TRUE),
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
   * @param   ParameterBagInterface  $parameters
   *   The parameters to this action.
   * @param   ParameterBagInterface  $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $round_number_precision = 2;
    if ($this->configuration->doesParameterExists('round_number_precision')) {
      $round_number_precision = $this->configuration->getParameter('round_number_precision');
    }

    $rounded_number = round($parameters->getParameter('round_number'), $round_number_precision);
    $output->setParameter('value', $rounded_number);
  }

}
