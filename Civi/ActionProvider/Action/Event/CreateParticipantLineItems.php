<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\ActionProvider\Parameter\SpecificationGroup;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Event;
use Civi\Api4\Participant;
use Civi\Api4\PriceFieldValue;
use CRM_ActionProvider_ExtensionUtil as E;

class CreateParticipantLineItems extends AbstractAction {

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contributionId = $parameters->getParameter('contribution_id');
    $selectedPriceOptions = EventActionUtils::getLineItemInfo($parameters);

    $apiParams['contribution_id'] = $contributionId;
    $apiParams['participant_id'] = $parameters->getParameter('participant_id');
    civicrm_api3('ParticipantPayment', 'create', $apiParams);

    foreach ($selectedPriceOptions as $priceOption) {
      try {
        if ($priceOption['tax_rate']) {
          $lineItemParams['tax_amount'] = $priceOption['tax_amount'] * $priceOption['qty'];
        }

        $lineItemParams['entity_table'] = 'civicrm_participant';
        $lineItemParams['entity_id'] = $parameters->getParameter('participant_id');
        $lineItemParams['participant_count'] = $priceOption['qty'];
        $lineItemParams['label'] = $priceOption['field_value']['label'];
        $lineItemParams['contribution_id'] = $contributionId;
        $lineItemParams['qty'] = $priceOption['qty'];
        $lineItemParams['unit_price'] = $priceOption['field_value']['amount'];
        $lineItemParams['financial_type_id'] = $priceOption['field_value']['financial_type_id'];
        $lineItemParams['price_field_value_id'] = $priceOption['field_value']['id'];
        $lineItemParams['price_field_id'] = $priceOption['field_value']['price_field_id.id'];
        // exit if unit price is zero value
        if ($lineItemParams['unit_price'] == 0) {
          continue;
        }

        // calculate line total + copy financial type
        $lineItemParams['line_total'] = $lineItemParams['unit_price'] * $lineItemParams['qty'];
        $new_lineitem = civicrm_api3('LineItem', 'create', $lineItemParams);

        // Get the lineitem object (not the array)
        $lineitem_param = ['id' => $new_lineitem['id']];
        $lineitem = \CRM_Price_BAO_LineItem::create($lineitem_param);
        $lineitem->find(TRUE);

        // Get the contribution object (not the array)
        $contribution_param = ['id' => $contributionId];
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

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      new Specification('price_field_value_id', 'Integer', E::ts('Event Price ID'), false),
      new Specification('price_set_field_values', 'Integer', E::ts('Price Set Field Value(s)'), false, null, 'PriceFieldValue', null, true),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), true),
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true),
    ]);
    return EventActionUtils::addPriceFieldsToSpecification($specs);
  }

  public function getOutputSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText()
  {
    return E::ts('You can use this action to create additional line items on an event contribution to register the payment for additional registrations. E.g. if there is one registration for two people and one contribution for those two persons. Each registration is then a line item at the contribution level.');
  }


}
