<?php
/**
 * @author Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class SendConfirmation extends AbstractAction {

  /**
   * @inheritDoc
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $params = $parameters->toArray();

    $params['id'] = $params['contribution_id'];
    unset($params['contribution_id']);

    $params = array_merge($this->configuration->toArray(), $params);

    civicrm_api3('Contribution', 'sendconfirmation', $params);
  }

  /**
   * @inheritDoc
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('payment_processor_id', 'Integer', E::ts('Payment processor'), false, null, 'PaymentProcessor'),
    ));
  }

  /**
   * @inheritDoc
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), true, null, null, null, FALSE),
      new Specification('receipt_from_email', 'Email', E::ts('From Email address')),
      new Specification('receipt_from_name', 'String', E::ts('From Name')),
      new Specification('cc_receipt', 'String', E::ts('CC Email address')),
      new Specification('bcc_receipt', 'String', E::ts('BCC Email address')),
      new Specification('receipt_text', 'Text', E::ts('Message')),
      new Specification('pay_later_receipt', 'Text', E::ts('Pay Later Message')),
      new Specification('receipt_update', 'Boolean', E::ts('Update the Receipt Date'), false, 0),
    ));
    return $specs;
  }
}