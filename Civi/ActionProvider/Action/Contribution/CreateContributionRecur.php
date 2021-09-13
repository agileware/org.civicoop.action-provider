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

class CreateContributionRecur extends AbstractAction {

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
    $contact_id = $parameters->getParameter('contact_id');

    // Create a contribution
    $contribution_params = CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification());
    $contribution_params['financial_type_id'] = $this->configuration->getParameter('financial_type_id');
    $contribution_params['payment_processor_id'] = $this->configuration->getParameter('payment_processor');
    $contribution_params['contact_id'] = $contact_id;
    $currency = null;
    if ($parameters->doesParameterExists('currency')) {
      $contribution_params['currency'] = $parameters->getParameter('currency');
      $currency = $parameters->getParameter('currency');
    }
    $contribution_params['amount'] = \CRM_Utils_Money::format((float) $parameters->getParameter('amount'), $currency, NULL, TRUE);
    if ($parameters->doesParameterExists('campaign_id')) {
      $contribution_params['campaign_id'] = $parameters->getParameter('campaign_id');
    }
    $contribution_params['frequency_interval'] = $parameters->getParameter('frequency_interval');
    $contribution_params['frequency_unit'] = $parameters->getParameter('frequency_unit');

    $result = civicrm_api3('ContributionRecur', 'Create', $contribution_params);

    $output->setParameter('contribution_recur_id', $result['id']);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('contribution_recur_id', 'Integer', E::ts('Contribution Recur ID'), false),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('financial_type_id', 'Integer', E::ts('Financial Type'), TRUE, null, 'FinancialType'),
      new Specification('payment_processor', 'Integer', E::ts('Payment Processor'), TRUE, null, 'PaymentProcessor'),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('amount', 'Float', E::ts('Amount'), true),
      new Specification('frequency_interval', 'Integer', E::ts('Frequency Interval'), true, 1),
      new OptionGroupSpecification('frequency_unit', 'recur_frequency_units', E::ts('Frequency Unit'), true),
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), false),
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
    ));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('ContributionRecur');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
  }

}
