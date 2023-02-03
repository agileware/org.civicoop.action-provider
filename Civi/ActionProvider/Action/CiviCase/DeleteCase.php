<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use API_Exception;
use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupByNameSpecification;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\CiviCase;
use CRM_ActionProvider_ExtensionUtil as E;

class DeleteCase extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Case ID'), true),
    ]);
    return $bag;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws UnauthorizedException|API_Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    CiviCase::delete()
      ->addWhere('id', '=', $parameters->getParameter('id'))
      ->execute();
  }



}
