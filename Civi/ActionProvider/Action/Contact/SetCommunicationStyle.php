<?php

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Set or update a contact's preferred communication methods
 *
 * @package Civi\ActionProvider\Action\Contact
 */
class SetCommunicationStyle extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
        new OptionGroupSpecification('value', 'communication_style', E::ts('Communication Style'), true),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
        new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([]);
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
    // the default way of getting the contact is this:
    $contact_id = $parameters->getParameter('contact_id');
    $value      = $this->configuration->getParameter('value');
    \civicrm_api3('Contact', 'create', ['id' => $contact_id, 'communication_style_id' => $value]);
  }
}
