<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Event;
use Civi\Api4\Participant;
use Civi\Api4\PriceFieldValue;
use CRM_ActionProvider_ExtensionUtil as E;

class CreateParticipantContribution extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $priceOptions = [];
    if ($parameters->doesParameterExists('price_set_field_values')) {
      $priceOptions = $parameters->getParameter('price_set_field_values');
    }
    if ($parameters->doesParameterExists('price_field_value_id')) {
      $priceOptions[] = $parameters->getParameter('price_field_value_id');
    }
    $qty = $parameters->getParameter('qty');
    if ($qty < 1) {
      $qty = 1;
    }

    $participant = Participant::get(FALSE)
      ->addSelect('*', 'event_id.*')
      ->addWhere('id', '=', $parameters->getParameter('participant_id'))
      ->execute()
      ->first();
    $event = Event::get(FALSE)
      ->addWhere('id', '=', $participant['event_id'])
      ->execute()
      ->first();

    $qty = $parameters->getParameter('qty');
    if ($qty < 1) {
      $qty = 1;
    }

    $total_amount = 0.00;
    $amount_level = '';
    foreach ($priceOptions as $priceOption) {
      if ($priceOption) {
        try {
          $priceFieldValue = \Civi\Api4\PriceFieldValue::get(FALSE)
            ->addWhere('id', '=', $priceOption)
            ->execute()
            ->first();
          $amount_level .= $priceFieldValue['label'] . ' - ' . $qty . ' ';
          $total_amount += $priceFieldValue['amount'] * $qty;
        }
        catch (UnauthorizedException|\CRM_Core_Exception $e) {

        }
      }
    }

    $contribParams['amount_level'] = $amount_level;
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
    $contribParams['total_amount'] = \CRM_Utils_Money::format((float) $total_amount, $currency, NULL, TRUE);
    $contribParams['financial_type_id'] = $event['financial_type_id'];
    $contribution = civicrm_api3('Contribution', 'Create', $contribParams);

    $apiParams['contribution_id'] = $contribution['id'];
    $apiParams['participant_id'] = $parameters->getParameter('participant_id');
    civicrm_api3('ParticipantPayment', 'create', $apiParams);

    foreach ($priceOptions as $priceOption) {
      if ($priceOption) {
        try {
          $priceFieldValue = PriceFieldValue::get(FALSE)
            ->addWhere('id', '=', $priceOption)
            ->execute()
            ->first();

          $lineItemParams['entity_table'] = 'civicrm_participant';
          $lineItemParams['entity_id'] = $parameters->getParameter('participant_id');
          $lineItemParams['participant_count'] = $qty;
          $lineItemParams['label'] = $priceFieldValue['label'];
          $lineItemParams['contribution_id'] = $contribution['id'];
          $lineItemParams['qty'] = $qty;
          $lineItemParams['unit_price'] = $priceFieldValue['amount'];
          $lineItemParams['financial_type_id'] = $priceFieldValue['financial_type_id'];
          $lineItemParams['price_field_value_id'] = $priceFieldValue['id'];
          $lineItemParams['price_field_id'] = $priceFieldValue['price_field_id.id'];
          // exit if unit price is zero value
          if ($lineItemParams['unit_price'] == 0) {
            continue;
          }

          // calculate line total + copy financial type
          $lineItemParams['line_total'] = $lineItemParams['unit_price'] * $lineItemParams['qty'];

          // if the financial type is not set then use the Contribution financial type
          if (empty($lineItemParams['financial_type_id'])) {
            $lineItemParams['financial_type_id'] = $contribParams['financial_type_id'];
          }
          $new_lineitem = civicrm_api3('LineItem', 'create', $lineItemParams);

          // Get the lineitem object (not the array)
          $lineitem_param = ['id' => $new_lineitem['id']];
          $lineitem = \CRM_Price_BAO_LineItem::create($lineitem_param);
          $lineitem->find(TRUE);

          // Get the contribution object (not the array)
          $contribution_param = ['id' => $contribution['id']];
          $contributionObj = \CRM_Contribute_BAO_Contribution::create($contribution_param);

          // Add the financial item
          \CRM_Financial_BAO_FinancialItem::add($lineitem, $contributionObj);

          // Add the financial item for tax
          if (!empty($lineitem->tax_amount)) {
            \CRM_Financial_BAO_FinancialItem::add($lineitem, $contributionObj, TRUE);
          }
        }
        catch (UnauthorizedException|\CRM_Core_Exception $e) {
        }
      }
    }

    $updateParticipantParams['id'] = $participant['id'];
    $updateParticipantParams['fee_level'] = $amount_level;
    $updateParticipantParams['fee_amount'] = $total_amount;
    $updateParticipantParams['fee_currency_id'] = $currency;
    civicrm_api3('Participant', 'create', $updateParticipantParams);

    $output->setParameter('contribution_id', $contribution['id']);
    $output->setParameter('amount', $total_amount);
    $output->setParameter('currency', $currency);
    $output->setParameter('amount_level', $amount_level);
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
