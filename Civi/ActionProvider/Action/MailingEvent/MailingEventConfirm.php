<?php
/**
 * @author Jens Schuppe <schuppe@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\MailingEvent;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class MailingEventConfirm extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('group_id', 'Integer', E::ts('Confirm mailing list subscription'), FALSE, NULL, 'Group'),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE, NULL, 'Contact'),
      new Specification('subscribe_id', 'Integer', E::ts('Subscribe Event ID'), TRUE, NULL, 'MailingEventSubscribe'),
      new Specification('hash', 'String', E::ts('Hash'), TRUE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }

  /**
   * Validates the input parameters.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return bool
   *
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  protected function validateParameters(ParameterBagInterface $parameters) {
    return parent::validateParameters($parameters);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $confirm_params = array(
      'contact_id' => $parameters->getParameter('contact_id'),
      'subscribe_id' => $parameters->getParameter('subscribe_id'),
      'hash' => $parameters->getParameter('hash'),
    );

    try {
      $result = civicrm_api3('MailingEventConfirm', 'Create', $confirm_params);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

}
