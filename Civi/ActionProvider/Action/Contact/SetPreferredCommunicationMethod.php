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
class SetPreferredCommunicationMethod extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $modes = [
        'add'    => E::ts("add"),
        'set'    => E::ts("set"),
        'unset'  => E::ts("unset"),
    ];

    return new SpecificationBag([
        new OptionGroupSpecification('value', 'preferred_communication_method', E::ts('Option'), true),
        new Specification('mode','String', E::ts('Mode'), true, null, null, $modes),
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
   * Get the current value of a contact's current communication methods
   *
   * @param $contact_id int contact ID
   * @return array list of active communication options
   */
  protected function getCurrentCommunicationMethods($contact_id) {
    $current_methods = \civicrm_api3('Contact', 'getValue', ['id' => $contact_id, 'return' => 'preferred_communication_method','options' => ['limit' => 1]]);
    if ($current_methods === '' || $current_methods === null) {
      $current_methods = [];
    } elseif (!is_array($current_methods)) {
      $current_methods = explode(',', (string) $current_methods);
    }
    return $current_methods;
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
    $mode       = $this->configuration->getParameter('mode');

    switch ($mode) {
      case 'set':
        // set the whole set to this (one) value
        \civicrm_api3('Contact', 'create', ['id' => $contact_id, 'preferred_communication_method' => [$value]]);
        break;

      case 'add':
        // add this option to the set
        $current_methods = $this->getCurrentCommunicationMethods($contact_id);
        if (!in_array($value, $current_methods)) {
          $current_methods[] = $value;
        }
        \civicrm_api3('Contact', 'create', ['id' => $contact_id, 'preferred_communication_method' => $current_methods]);
        break;

      case 'remove':
        // remove this option to the set
        $current_methods = $this->getCurrentCommunicationMethods($contact_id);
        if (($key = array_search($value, $current_methods)) !== false) {
          unset($current_methods[$key]);
          \civicrm_api3('Contact', 'create', ['id' => $contact_id, 'preferred_communication_method' => $current_methods]);
        }
        break;
    }
  }
}