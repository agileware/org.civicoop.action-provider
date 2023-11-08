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
    $priceFieldValue = \Civi\Api4\PriceFieldValue::get(FALSE)
      ->addSelect('*', 'price_field_id.*')
      ->addWhere('id', '=', $parameters->getParameter('price_field_value_id'))
      ->execute()
      ->first();
    $qty = $parameters->getParameter('qty');
    if ($qty < 1) {
      $qty = 1;
    }
    $amount_level = $priceFieldValue['price_field_id.label'] . ' - ' . $priceFieldValue['label'];
    if ($qty > 1) {
      $amount_level .= E::ts(' (multiple participants)');
    }
    $total_amount = $priceFieldValue['amount'] * $qty;
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
      new Specification('price_field_value_id', 'Integer', E::ts('Event Price ID'), true),
      new Specification('qty', 'Integer', E::ts('Number of registrations'), true, 1),
    ]);
  }

  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('amount', 'Float', E::ts('Amount'), false),
      new Specification('amount_level', 'String', E::ts('Amount Level'), false),
    ]);
  }

}
