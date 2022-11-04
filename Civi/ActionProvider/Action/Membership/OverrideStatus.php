<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Membership;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class OverrideStatus extends AbstractAction {

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
    $membership_id = $parameters->getParameter('membership_id');
    $status_id = $this->configuration->getParameter('status');
    $apiParams = array();
    $apiParams['id'] = $membership_id;
    $apiParams['is_override'] = '1';
    $apiParams['status_id'] = $status_id;
    if ($parameters->doesParameterExists('status_override_end_date')) {
      $apiParams['status_override_end_date'] = $parameters->getParameter('status_override_end_date');
    }

    civicrm_api3('Membership', 'create', $apiParams);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('status', 'Integer', E::ts('Status'), true, null, 'MembershipStatus'),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('membership_id', 'Integer', E::ts('Membership ID'), true),
      new Specification('status_override_end_date', 'Date', E::ts('Status Override End Date'), false)
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
    return new SpecificationBag([]);
  }


}
