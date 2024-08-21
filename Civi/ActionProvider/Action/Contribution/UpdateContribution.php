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

class UpdateContribution extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contribution_params = CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification());
    $contribution_params['id'] = $parameters->getParameter('id');

    if ($this->configuration->doesParameterExists('is_pay_later')) {
      $contribution_params['is_pay_later'] = $this->configuration->getParameter('is_pay_later') ? TRUE : FALSE;
    }

    if ($parameters->doesParameterExists('financial_type_id')) {
      $contribution_params['financial_type_id'] = $parameters->getParameter('financial_type_id');
    } elseif ($this->configuration->doesParameterExists('financial_type_id')) {
      $contribution_params['financial_type_id'] = $this->configuration->getParameter('financial_type_id');
    }
    if ($parameters->doesParameterExists('contribution_status')) {
      $contribution_params['contribution_status_id'] = $parameters->getParameter('contribution_status');
    } elseif ($this->configuration->doesParameterExists('contribution_status')) {
      $contribution_params['contribution_status_id'] = $this->configuration->getParameter('contribution_status');
    }
    if ($parameters->doesParameterExists('payment_instrument')) {
      $contribution_params['payment_instrument_id'] = $parameters->getParameter('payment_instrument');
    } elseif ($this->configuration->doesParameterExists('payment_instrument')) {
      $contribution_params['payment_instrument_id'] = $this->configuration->getParameter('payment_instrument');
    }
    if ($parameters->doesParameterExists('contact_id')) {
      $contribution_params['contact_id'] = $parameters->getParameter('contact_id');;
    }
    if ($parameters->doesParameterExists('currency')) {
      $contribution_params['currency'] = $parameters->getParameter('currency');
    }
    if ($parameters->doesParameterExists('amount')) {
      $contribution_params['total_amount'] = (float) $parameters->getParameter('amount');
    }
    if ($parameters->doesParameterExists('source')) {
      $contribution_params['source'] = $parameters->getParameter('source');
    }
    if ($parameters->doesParameterExists('campaign_id')) {
      $contribution_params['campaign_id'] = $parameters->getParameter('campaign_id');
    }
    if ($parameters->doesParameterExists('contribution_recur_id')) {
      $contribution_params['contribution_recur_id'] = $parameters->getParameter('contribution_recur_id');
    }
    if ($parameters->doesParameterExists('trxn_id')) {
      $contribution_params['trxn_id'] = $parameters->getParameter('trxn_id');
    }
    if ($parameters->doesParameterExists('receive_date')) {
      $contribution_params['receive_date'] = $parameters->getParameter('receive_date');
    }
    if ($parameters->doesParameterExists('receipt_date')) {
      $contribution_params['receipt_date'] = $parameters->getParameter('receipt_date');
    }
    if ($parameters->doesParameterExists('note')) {
      $contribution_params['note'] = $parameters->getParameter('note');
    }

    $result = civicrm_api3('Contribution', 'Create', $contribution_params);

    $output->setParameter('contribution_id', $result['id']);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), FALSE),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('financial_type_id', 'Integer', E::ts('Financial Type'), FALSE, NULL, 'FinancialType'),
      new OptionGroupSpecification('payment_instrument', 'payment_instrument', E::ts('Payment instrument'), FALSE),
      new OptionGroupSpecification('contribution_status', 'contribution_status', E::ts('Status of contribution'), FALSE),
      new Specification('is_pay_later', 'Boolean', E::ts('Is Pay Later'), FALSE),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Contribution ID'), TRUE),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE),
      new Specification('amount', 'Float', E::ts('Amount'), FALSE),
      new Specification('financial_type_id', 'Integer', E::ts('Financial Type'), FALSE, NULL, 'FinancialType'),
      new OptionGroupSpecification('payment_instrument', 'payment_instrument', E::ts('Payment instrument'), FALSE),
      new OptionGroupSpecification('contribution_status', 'contribution_status', E::ts('Status of contribution'), FALSE),
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), FALSE),
      new Specification('contribution_recur_id', 'Integer', E::ts('Contribution Recur ID'), FALSE),
      new Specification('receive_date', 'Date', E::ts('Receive date'), FALSE),
      new Specification('receipt_date', 'Date', E::ts('Receipt date'), FALSE),
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
      new Specification('source', 'String', E::ts('Source'), FALSE),
      new Specification('note', 'String', E::ts('Note'), FALSE),
      new Specification('trxn_id', 'String', E::ts('Transaction ID'), FALSE),
    ]);

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Contribution');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
  }

}
