<?php
/**
 * @author  Agileware Projects <projects@agileware.com.au>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Exception\BreakException;
use \Civi\ActionProvider\Exception\ExecutionException;
use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class ThrowException extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   *
   * @throws \Civi\ActionProvider\Exception\BreakException|\Civi\ActionProvider\Exception\ExecutionException
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Note: This error message is passed to the exception handler but may not be shown anywhere other than the CiviCRM Admin UI

    $error_message = $this->configuration->getParameter('error_message');
    $report_as_error = $this->configuration->getParameter('report_as_error');

    if ($parameters->doesParameterExists('error_message')) {
      $error_message = $parameters->getParameter('error_message');
    }

    if($report_as_error) {
      throw new ExecutionException($error_message, 0, NULL, FALSE);
    }
    else {
      throw new BreakException($error_message, 0, NULL);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('error_message', 'String', E::ts('Error Message'), false),
      new Specification('report_as_error', 'Boolean', E::ts('Report as error'), true, false ),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('error_message', 'String', E::ts('Error Message'), false),
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
    return new SpecificationBag();
  }

}
