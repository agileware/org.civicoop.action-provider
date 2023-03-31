<?php
/**
 * @author Justin Freeman <support@agileware.com.au>
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

class CreatePayment extends AbstractAction {

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
    $contribution_params['contribution_id'] = $parameters->getParameter('contribution_id');

    if ($this->configuration->doesParameterExists('payment_instrument')) {
      $contribution_params['payment_instrument_id'] = $this->configuration->getParameter('payment_instrument');
    }
    if ($parameters->doesParameterExists('total_amount')) {
      $contribution_params['total_amount'] = (float) $parameters->getParameter('total_amount');
    }
    if ($parameters->doesParameterExists('trxn_id')) {
      $contribution_params['trxn_id'] = $parameters->getParameter('trxn_id');
    }
    if ($parameters->doesParameterExists('trxn_date')) {
      $contribution_params['trxn_date'] = $parameters->getParameter('trxn_date');
    }
    if ($parameters->doesParameterExists('payment_instrument')) {
      $contribution_params['payment_instrument_id'] = $parameters->getParameter('payment_instrument');
    }

    $result = civicrm_api3('Payment', 'Create', $contribution_params);

    $output->setParameter('payment_id', $result['id']);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), false),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new OptionGroupSpecification('payment_instrument', 'payment_instrument', E::ts('Payment instrument'), TRUE),
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
      new Specification('total_amount', 'Float', E::ts('Amount'), true),
      new OptionGroupSpecification('payment_instrument', 'payment_instrument', E::ts('Payment instrument'), false),
      new Specification('trxn_date', 'Date', E::ts('Transaction date'), false),
      new Specification('trxn_id', 'String', E::ts('Transaction ID'), false),
    ));
  }

}
