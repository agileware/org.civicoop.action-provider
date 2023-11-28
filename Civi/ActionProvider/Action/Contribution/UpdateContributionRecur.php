<?php
/**
 * @author  Agileware <projects@agileware.com.au>
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

class UpdateContributionRecur extends AbstractAction {

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
    $contribution_params['id'] = $parameters->getParameter('contribution_recur_id');

    if ($parameters->doesParameterExists('contact_id')) {
      $contribution_params['contact_id'] = $parameters->getParameter('contact_id');
    }

    if ($parameters->doesParameterExists('payment_token_id')) {
      $contribution_params['payment_token_id'] = $parameters->getParameter('payment_token_id');
    }

    if ($parameters->doesParameterExists('financial_type_id')) {
      $contribution_params['financial_type_id'] = $parameters->getParameter('financial_type_id');
    }

    if ($parameters->doesParameterExists('status_id')) {
      $contribution_params['status_id'] = $parameters->getParameter('status_id');
    }

    if ($parameters->doesParameterExists('amount')) {
      $contribution_params['amount'] = $parameters->getParameter('amount');
    }

    if ($parameters->doesParameterExists('frequency_interval')) {
      $contribution_params['frequency_interval'] = $parameters->getParameter('frequency_interval');
    }

    if ($parameters->doesParameterExists('frequency_unit')) {
      $contribution_params['frequency_unit'] = $parameters->getParameter('frequency_unit');
    }

    if ($parameters->doesParameterExists('campaign_id')) {
      $contribution_params['campaign_id'] = $parameters->getParameter('campaign_id');
    }

    $contribution_params = array_merge($contribution_params,CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification()));

    $currency = NULL;
    if ($parameters->doesParameterExists('currency')) {
      $currency                        = $parameters->getParameter('currency');
      $contribution_params['currency'] = $currency;
    }

    if ($parameters->doesParameterExists('amount')) {
      $contribution_params['amount'] = \CRM_Utils_Money::format((float) $parameters->getParameter('amount'), $currency, NULL, TRUE);
    }

    $result = civicrm_api3('ContributionRecur', 'Create', $contribution_params);

    $output->setParameter('contribution_recur_id', $result['id']);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('contribution_recur_id', 'Integer', E::ts('Contribution Recur ID'), FALSE),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      new Specification('contribution_recur_id', 'Integer', E::ts('Recurring Contribution ID'), TRUE),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE),
      new Specification('payment_token_id', 'Integer', E::ts('Payment Token ID'), FALSE),
      new Specification('financial_type_id', 'Integer', E::ts('Financial Type'), FALSE, NULL, 'FinancialType'),
      new OptionGroupSpecification('status_id', 'contribution_recur_status', E::ts('Status'), FALSE),
      new Specification('amount', 'Float', E::ts('Amount'), FALSE),
      new Specification('frequency_interval', 'Integer', E::ts('Frequency Interval'), FALSE),
      new OptionGroupSpecification('frequency_unit', 'recur_frequency_units', E::ts('Frequency Unit'), FALSE),
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), FALSE),
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
    ]);

    $config       = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('ContributionRecur');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
  }

}
