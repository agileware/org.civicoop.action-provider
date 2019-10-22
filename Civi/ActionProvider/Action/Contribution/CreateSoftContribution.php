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

class CreateSoftContribution extends AbstractAction {

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
    $id = $parameters->getParameter('contribution_id');
    $cid = $parameters->getParameter('contact_id');
    $contribution = civicrm_api3('Contribution', 'getsingle', array('id' => $id));
    $apiParams['contribution_id'] = $id;
    $apiParams['amount'] = $contribution['total_amount'];
    $apiParams['contact_id'] = $cid;
    $apiParams['soft_credit_type_id'] = $this->configuration->getParameter('soft_credit_type');
    civicrm_api3('ContributionSoft', 'create', $apiParams);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $softCreditTypeApi = civicrm_api3('ContributionSoft', 'getoptions', array(
      'field' => 'soft_credit_type_id',
      'options' => array('limit' => 0),
    ));
    return new SpecificationBag(array(
      new Specification('soft_credit_type', 'Integer', E::ts('Soft credit type'), true, null, null, $softCreditTypeApi['values']),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), true),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ));
  }

}
