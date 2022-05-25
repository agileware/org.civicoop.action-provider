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

class MailingEventUnsubscribe extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('is_opt_out', 'Boolean', E::ts('Is opt out'), FALSE, FALSE),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('hash', 'String', E::ts('Hash'), TRUE),
      new Specification('job_id', 'Integer', E::ts('Job Id'), TRUE),
      new Specification('event_queue_id', 'Integer', E::ts('Event Queue ID'), TRUE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array());
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
    $unsubscribe_params['hash'] = $parameters->getParameter('hash');
    $unsubscribe_params['event_queue_id'] = $parameters->getParameter('event_queue_id');
    $unsubscribe_params['job_id'] = $parameters->getParameter('job_id');
    if ($this->getConfiguration()->getParameter('is_opt_out')) {
      $unsubscribe_params['org_unsubscribe'] = '1';
    }

    try {
      civicrm_api3('MailingEventUnsubscribe', 'Create', $unsubscribe_params);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

}
