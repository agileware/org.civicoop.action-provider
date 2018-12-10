<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateParticipantStatusWithDynamicStatus extends AbstractAction {
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Update participant status (with dynamic status)');
  }
  
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
      new Specification('status', 'Integer', E::ts('Status'), true, null, 'ParticipantStatusType', null, FALSE),
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
    return new SpecificationBag(array(
      new Specification('old_status_id', 'Integer', E::ts('Old status ID')),
      new Specification('new_status_id', 'Integer', E::ts('New status ID')),
    ));
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
    
    $output->setParameter('old_status_id', null);
    $output->setParameter('new_status_id', null);
    
    // Find the participant record for this contact and event. 
    // This assumes that the contact has already been registered for the event.
    $participant_id = \CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_participant WHERE is_test = '0' AND contact_id = %1 AND event_id = %2 ORDER BY id DESC LIMIT 1", array(
      1 => array($contact_id, 'Integer'),
      2 => array($event_id, 'Integer'),
    ));
    if (!$participant_id) {
      // No record is found. 
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not find a participant record'));
    }
    
    //Find the old status ID
    $old_status_id = \CRM_Core_DAO::singleValueQuery("SELECT status_id FROM civicrm_participant WHERE id = %1", array(
      1 => array($participant_id, 'Integer'),
    ));
    if ($old_status_id) {
      $output->setParameter('old_status_id', $old_status_id);
    }
    
    $new_status_id = $parameters->getParameter('status');
    // Update the participant record through an API call.
    try {
      civicrm_api3('Participant', 'create', array(
        'id' => $participant_id,
        'status_id' => $new_status_id,
        'check_permissions' => false,
      ));
      $output->setParameter('new_status_id', $new_status_id);
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not update participant status'));
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