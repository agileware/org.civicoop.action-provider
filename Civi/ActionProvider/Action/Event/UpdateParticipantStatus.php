<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateParticipantStatus extends AbstractAction {
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Update participant status'); 
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() { 
    return new SpecificationBag(array(
      new Specification('status', 'Integer', E::ts('Status'), true, null, 'ParticipantStatusType', null, FALSE),
    ));
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
    return new SpecificationBag();
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
    $participant = civicrm_api3('Participant', 'get', array(
      'contact_id' => $contact_id,
      'event_id' => $event_id,
      'options' => array('limit' => 1),
    ));
    if ($participant['count'] < 1) {
      // No record is found. 
      throw new \Civi\ActionProvider\Action\Exception\ExecutionException(E::ts('Could not find a participant record'));
    }
    
    // Get the participant record and the status id from the configuration.
    $participant = reset($participant['values']);
    $new_status_id = $this->configuration->getParameter('status');
    
    // Update the participant record through an API call.
    try {
      civicrm_api3('Participant', 'create', array(
        'id' => $participant['id'],
        'status_id' => $new_status_id,
      ));
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Action\Exception\ExecutionException(E::ts('Could not update participant status'));
    }
  }

  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    );
  }
  
}