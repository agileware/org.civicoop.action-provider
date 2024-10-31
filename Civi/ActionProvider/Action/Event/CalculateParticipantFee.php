<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class CalculateParticipantFee extends AbstractAction {

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

    $total_amount = 0.00;
    $amount_level = '';
    foreach ($priceOptions as $priceOption) {
      $priceFieldValue = \Civi\Api4\PriceFieldValue::get(FALSE)
        ->addWhere('id', '=', $priceOption)
        ->execute()
        ->first();
      $amount_level .= $priceFieldValue['label'] . ' - ' . $qty .' ';
      $total_amount += $priceFieldValue['amount'] * $qty;
    }
    if ($qty > 1) {
      $amount_level .= E::ts(' (multiple participants)');
    }
    $amount_level = trim($amount_level);
    $output->setParameter('amount', $total_amount);
    $output->setParameter('amount_level', $amount_level);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('price_set_field_values', 'Integer', E::ts('Price Set Field Value(s)'), false, null, 'PriceFieldValue', null, true),
      new Specification('price_field_value_id', 'Integer', E::ts('Event Price ID'), false),
      new Specification('qty', 'Integer', E::ts('Number of registrations'), false, 1),
    ]);
  }

  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('amount', 'Float', E::ts('Amount'), false),
      new Specification('amount_level', 'String', E::ts('Amount Level'), false),
    ]);
  }

}
