<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\ActionProvider\Parameter\SpecificationGroup;
use Civi\API\Exception\UnauthorizedException;
use CRM_ActionProvider_ExtensionUtil as E;

class CalculateParticipantFee extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $currency = null;
    if ($this->configuration->doesParameterExists('currency')) {
      $currency = $this->configuration->getParameter('currency');
    }
    $qty = $parameters->getParameter('qty');
    if ($qty < 1) {
      $qty = 1;
    }
    $selectedPriceOptions = EventActionUtils::getLineItemInfo($parameters);

    $lines = [];
    $subtotal_amount = 0;
    $total_amount = 0.00;
    $total_tax_amounts = [];
    $amount_level = '';
    foreach ($selectedPriceOptions as $priceOption) {
      $amount_without_tax = $priceOption['amount'];
      $amount = $priceOption['amount'];
      if ($priceOption['tax_rate']) {
        if (!isset($total_tax_amounts[$priceOption['tax_rate']])) {
          $total_tax_amounts[$priceOption['tax_rate']] = 0;
        }
        $amount += $priceOption['tax_amount'];
        $total_tax_amounts[$priceOption['tax_rate']] += $priceOption['tax_amount'] * $priceOption['qty'];
      }

      $amount_level .= $priceOption['field_value']['label'] . ' - ' . $priceOption['qty'] . ' ';
      $total_amount += $amount * $priceOption['qty'];
      $subtotal_amount += $amount_without_tax * $priceOption['qty'];
      $lines[] = [
        'qty' => $priceOption['qty'],
        'label' => $priceOption['field_value']['label'],
        'amount' => $priceOption['amount'] * $priceOption['qty'],
        'currency' => $currency,
      ];
    }
    if ($qty > 1 && strlen($amount_level)) {
      $amount_level .= E::ts(' (multiple participants)');
    }
    $amount_level = trim($amount_level);
    $output->setParameter('amount', $total_amount);
    $output->setParameter('amount_level', $amount_level);

    $htmlCart = '';
    foreach ($lines as $line) {
      $formattedAmount = \CRM_Utils_Money::format($line['amount'], $line['currency']);
      $htmlCart .= '<p class="line"><span class="label">' . $line['label'] . '</span> <span class="qty">'.$line['qty'].'</span> <span class="amount">'.$formattedAmount.'</span></p>';
    }
    $formattedAmount = \CRM_Utils_Money::format($subtotal_amount, $currency);
    $htmlCart .= '<p class="line sub-total"><span class="label">'.E::ts('Subtotal amount').'</span><span class="amount">' . $formattedAmount.'</span></p>';
    foreach ($total_tax_amounts as $taxRate => $taxAmount) {
      $formattedAmount = \CRM_Utils_Money::format($taxAmount, $currency);
      $htmlCart .= '<p class="line tax tax-'.$taxRate.'"><span class="label">'.E::ts('%1 VAT', [1=>$taxRate . '%']).'</span><span class="amount">' . $formattedAmount.'</span></p>';
    }
    $formattedAmount = \CRM_Utils_Money::format($total_amount, $currency);
    $htmlCart .= '<p class="line total"><span class="label">'.E::ts('Total amount').'</span><span class="amount">' . $formattedAmount.'</span></p>';
    $output->setParameter('html_line_items', $htmlCart);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      new Specification('price_set_field_values', 'Integer', E::ts('Price Set Field Value(s)'), false, null, 'PriceFieldValue', null, true),
      new Specification('price_field_value_id', 'Integer', E::ts('Event Price ID'), false),
      new Specification('qty', 'Integer', E::ts('Number of registrations'), false, 1),
    ]);
    return EventActionUtils::addPriceFieldsToSpecification($specs);
  }

  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('amount', 'Float', E::ts('Amount'), false),
      new Specification('amount_level', 'String', E::ts('Amount Level'), false),
      new Specification('html_line_items', 'String', E::ts('Line Items in HTML'), false),
    ]);
  }

}
