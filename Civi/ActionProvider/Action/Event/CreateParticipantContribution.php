<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateParticipantContribution extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $participant = \Civi\Api4\Participant::get(FALSE)
      ->addSelect('*', 'event_id.*')
      ->addWhere('id', '=', $parameters->getParameter('participant_id'))
      ->execute()
      ->first();
    $priceFieldValue = \Civi\Api4\PriceFieldValue::get(FALSE)
      ->addSelect('*', 'price_field_id.*')
      ->addWhere('id', '=', $parameters->getParameter('price_field_value_id'))
      ->execute()
      ->first();
    $qty = $parameters->getParameter('qty');
    if ($qty < 1) {
      $qty = 1;
    }
    $contribParams['amount_level'] = $priceFieldValue['price_field_id.label'] . ' - ' . $priceFieldValue['label'];
    if ($qty > 1) {
      $contribParams['amount_level'] .= E::ts(' (multiple participants)');
    }
    $currency = null;
    $config = \CRM_Core_Config::singleton();
    if ($parameters->doesParameterExists('currency')) {
      $currency = $parameters->getParameter('currency');
    }
    if (!$currency) {
      $currency = $config->defaultCurrency;
    }
    $allContributionStatuses = \CRM_Contribute_PseudoConstant::contributionStatus(NULL, 'name');
    $contribParams['source'] = $participant['event_id.title'];
    $contribParams['contribution_status_id'] = array_search('Completed', $allContributionStatuses);
    if ($this->configuration->getParameter('is_pay_later')) {
      $contribParams['contribution_status_id'] = array_search('Pending', $allContributionStatuses);
    }
    $contribParams['contact_id'] = $participant['contact_id'];
    $contribParams['currency'] = $currency;
    $contribParams['is_pay_later'] = $this->configuration->getParameter('is_pay_later') ? true : false;
    $contribParams['skipLineItem'] =
    $total_amount = $priceFieldValue['amount'] * $qty;
    $contribParams['total_amount'] = \CRM_Utils_Money::format((float) $total_amount, $currency, NULL, TRUE);
    $contribParams['financial_type_id'] = $priceFieldValue['financial_type_id'];
    $contribution = civicrm_api3('Contribution', 'Create', $contribParams);

    $apiParams['contribution_id'] = $contribution['id'];
    $apiParams['participant_id'] = $parameters->getParameter('participant_id');
    civicrm_api3('ParticipantPayment', 'create', $apiParams);

    $lineItemParams['entity_table'] = 'civicrm_participant';
    $lineItemParams['entity_id'] = $parameters->getParameter('participant_id');
    $lineItemParams['participant_count'] = $qty;
    $lineItemParams['label'] = $priceFieldValue['label'];
    $lineItemParams['contribution_id'] = $contribution['id'];
    $lineItemParams['qty'] = $qty;
    $lineItemParams['unit_price'] = $priceFieldValue['amount'];
    $lineItemParams['line_total'] = $total_amount;
    $lineItemParams['financial_type_id'] = $priceFieldValue['financial_type_id'];
    $lineItemParams['price_field_value_id'] = $priceFieldValue['id'];
    $lineItemParams['price_field_id'] = $priceFieldValue['price_field_id.id'];
    civicrm_api3('LineItem', 'create', $lineItemParams);

    $updateParticipantParams['id'] = $participant['id'];
    $updateParticipantParams['fee_level'] = $priceFieldValue['label'] . ' - ' . $qty;
    $updateParticipantParams['fee_amount'] = $total_amount;
    $updateParticipantParams['fee_currency_id'] = $currency;
    civicrm_api3('Participant', 'create', $updateParticipantParams);

    $output->setParameter('contribution_id', $contribution['id']);
    $output->setParameter('amount', $total_amount);
    $output->setParameter('currency', $currency);
    $output->setParameter('amount_level', $contribParams['amount_level']);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('is_pay_later', 'Boolean', E::ts('Is Pay Later'), true, false),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('price_field_value_id', 'Integer', E::ts('Event Price ID'), true),
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true),
      new Specification('qty', 'Integer', E::ts('Number of registrations'), true, 1),
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
    ]);
  }

  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), false),
      new Specification('amount', 'Float', E::ts('Amount'), false),
      new Specification('amount_level', 'String', E::ts('Amount Level'), false),
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
    ]);
  }

}
