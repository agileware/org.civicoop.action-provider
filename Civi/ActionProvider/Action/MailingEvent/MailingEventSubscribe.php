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

class MailingEventSubscribe extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('group_id', 'Integer', E::ts('Subscribe to group'), FALSE, NULL, 'Group'),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('email', 'String', E::ts('Subscribe e-mail'), TRUE),
      new Specification('group_id', 'Integer', E::ts('Subscribe to group'), FALSE, NULL, 'Group'),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE, NULL, 'Contact'),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('id', 'Integer', E::ts('Subscription ID')),
    ));
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
    // Either configuration or parameter for group_id must be present.
    $configuration = $this->getConfiguration();
    if (!$parameters->doesParameterExists('group_id') && !$configuration->doesParameterExists('group_id')) {
      throw new InvalidParameterException('group_id is required');
    }

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
    // Get default group ID from configuration.
    if ($this->configuration->getParameter('group_id')) {
      $subscribe_params['group_id'] = $this->configuration->getParameter('group_id');
    }
    // Overwrite with group ID from parameters if given.
    if ($parameters->doesParameterExists('group_id')) {
      $subscribe_params['group_id'] = $parameters->getParameter('group_id');
    }

    $subscribe_params['email'] = $parameters->getParameter('email');
    if ($parameters->doesParameterExists('contact_id')) {
      $subscribe_params['contact_id'] = $parameters->getParameter('contact_id');
    }

    try {
      $result = civicrm_api3('MailingEventSubscribe', 'Create', $subscribe_params);
      $output->setParameter('id', $result['id']);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

}
