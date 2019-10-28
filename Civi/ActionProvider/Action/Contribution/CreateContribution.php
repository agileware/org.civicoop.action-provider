<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\ParameterBagInterface;

use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class CreateContribution extends AbstractAction {

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
    $contribution_params['financial_type_id'] = $this->configuration->getParameter('financial_type_id');
    $contribution_params['contribution_status_id'] = $this->configuration->getParameter('contribution_status');
    $contribution_params['payment_instrument_id'] = $this->configuration->getParameter('payment_instrument');
    $contribution_params['contact_id'] = $contact_id;
    if ($parameters->doesParameterExists('currency')) {
      $contribution_params['currency'] = $parameters->getParameter('currency');
    }
    $contribution_params['total_amount'] = (float) $parameters->getParameter('amount');
    if ($parameters->doesParameterExists('source')) {
      $contribution_params['source'] = $parameters->getParameter('source');
    }
    if ($parameters->doesParameterExists('campaign_id')) {
      $contribution_params['campaign_id'] = $parameters->getParameter('campaign_id');
    }
    if ($parameters->doesParameterExists('contribution_recur_id')) {
      $contribution_params['contribution_recur_id'] = $parameters->getParameter('contribution_recur_id');
    }
    if ($parameters->doesParameterExists('receive_date')) {
      $contribution_params['receive_date'] = $parameters->getParameter('receive_date');
    }

    foreach($this->getParameterSpecification() as $spec) {
      if (stripos($spec->getName(), 'custom_')!==0) {
        continue;
      }
      if ($parameters->doesParameterExists($spec->getName())) {
        $contribution_params[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
      }
    }

    $result = civicrm_api3('Contribution', 'Create', $contribution_params);

    $output->setParameter('contribution_id', $result['id']);
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
      new Specification('financial_type_id', 'Integer', E::ts('Financial Type'), TRUE, null, 'FinancialType'),
      new OptionGroupSpecification('payment_instrument', 'payment_instrument', E::ts('Payment instrument'), TRUE),
      new OptionGroupSpecification('contribution_status', 'contribution_status', E::ts('Status of contribution'), TRUE),
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
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), false),
      new Specification('contribution_recur_id', 'Integer', E::ts('Contribution Recur ID'), false),
      new Specification('receive_date', 'Date', E::ts('Receive date'), false),
      new OptionGroupSpecification('currency', 'currencies_enabled', E::ts('Currency'), FALSE),
      new Specification('source', 'String', E::ts('Source'), false),
    ));

    $customGroups = civicrm_api3('CustomGroup', 'get', array('extends' => 'Contribution', 'is_active' => 1, 'options' => array('limit' => 0)));
    foreach($customGroups['values'] as $customGroup) {
      $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroup['id'], 'is_active' => 1, 'options' => array('limit' => 0)));
      foreach($customFields['values'] as $customField) {
        $spec = CustomField::getSpecFromCustomField($customField, $customGroup['title'].': ', false);
        if ($spec) {
          $specs->addSpecification($spec);
        }
      }
    }
    return $specs;
  }

}
