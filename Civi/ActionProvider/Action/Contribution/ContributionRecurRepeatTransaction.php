<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\ParameterBagInterface;

use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class ContributionRecurRepeatTransaction extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contribution_params['contribution_recur_id'] = $parameters->getParameter('contribution_recur_id');
    if ($parameters->doesParameterExists('original_contribution_id')) {
      $contribution_params['original_contribution_id'] = $parameters->getParameter('original_contribution_id');
    }
    $contribution_params['is_email_receipt'] = $this->configuration->getParameter('is_email_receipt');
    $result = civicrm_api3('Contribution', 'repeattransaction', $contribution_params);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('is_email_receipt', 'Boolean', E::ts('Send e-mail receipt'), true),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      new Specification('contribution_recur_id', 'Integer', E::ts('Contribution Recur ID'), true),
      new Specification('original_contribution_id', 'Integer', E::ts('Original Contribution ID'), false),
    ));
    return $specs;
  }

}
