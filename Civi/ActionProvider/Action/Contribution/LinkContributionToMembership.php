<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class LinkContributionToMembership extends AbstractAction {

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
    $apiParams['contribution_id'] = $parameters->getParameter('contribution_id');
    $apiParams['membership_id'] = $parameters->getParameter('membership_id');
    // The caller will have taken responsibility for updating the line items themselves
    $apiParams['isSkipLineItem'] = TRUE;
    civicrm_api3('MembershipPayment', 'create', $apiParams);

    if ($this->configuration->getParameter('set_pending')) {
      $pendingMembershipStatusId = \CRM_Core_PseudoConstant::getKey('CRM_Member_BAO_Membership', 'status_id', 'Pending');
      $membershipApiParams['status_id'] = $pendingMembershipStatusId;
      $membershipApiParams['skipStatusCal'] = TRUE;
      $membershipApiParams['is_pay_later'] = 1;
      $membershipApiParams['id'] = $parameters->getParameter('membership_id');
      civicrm_api3('Membership', 'create', $membershipApiParams);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('set_pending', 'Boolean', E::ts('Set membership to pending'), false, false)
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('membership_id', 'Integer', E::ts('Membership ID'), true),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), true),
    ));
  }


}
