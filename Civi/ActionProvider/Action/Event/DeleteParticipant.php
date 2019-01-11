<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class DeleteParticipant extends AbstractAction {
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() { 
    return new SpecificationBag(array());
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() { 
    return new SpecificationBag(array(
      /**
       * The parameters given to the Specification object are:
       * @param string $name
       * @param string $dataType
       * @param string $title
       * @param bool $required
       * @param mixed $defaultValue
       * @param string|null $fkEntity
       * @param array $options
       * @param bool $multiple
       */
      new Specification('event_id', 'Integer', E::ts('Event ID'), true, null, null, null, FALSE),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true, null, null, null, FALSE),
    ));
  }
  
  /**
   * Returns the specification of the output parameters of this action.
   * 
   * This function could be overriden by child classes.
   * 
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array());
  }
  
  /**
   * Run the action
   * 
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back 
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Get the contact and the event.
    $contact_id = $parameters->getParameter('contact_id');
    $event_id = $parameters->getParameter('event_id');
    
    // Find the participant record for this contact and event. 
    // This assumes that the contact has already been registered for the event.
    $participant_id = \CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_participant WHERE is_test = '0' AND contact_id = %1 AND event_id = %2 ORDER BY id DESC LIMIT 1", array(
      1 => array($contact_id, 'Integer'),
      2 => array($event_id, 'Integer'),
    ));
    if (!$participant_id) {
      // No record is found. 
      return;
    }

    // Update the participant record through an API call.
    try {
      civicrm_api3('Participant', 'delete', array(
        'id' => $participant_id,
        'check_permissions' => false,
      ));
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not delete participant'));
    }
  }
  
}