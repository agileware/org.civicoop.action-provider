<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateOrUpdateParticipant extends AbstractAction {
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Register contact for an event'); 
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('status_id', 'Integer', E::ts('Status'), true, null, 'ParticipantStatusType', null, FALSE),
      new OptionGroupSpecification('role_id', 'participant_role', E::ts('Role'), true, null, FALSE),
      new Specification('update_existing', 'Boolean', E::ts('Update existing event registration'), false, 0, null, null, FALSE),
      new Specification('source', 'String', E::ts('Source'), false, null, null, null, FALSE),
    ));
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() { 
    $specs = new SpecificationBag(array(
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
      new Specification('campaign_id', 'Integer', E::ts('Campaign ID'), false, null, null, null, FALSE),
    ));
    
    $customGroups = civicrm_api3('CustomGroup', 'get', array('extends' => 'Participant', 'is_active' => 1, 'options' => array('limit' => 0)));
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
  
  /**
   * Returns the specification of the output parameters of this action.
   * 
   * This function could be overriden by child classes.
   * 
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('id', 'Integer', E::ts('Participant record ID')),
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
    $role_id = $this->configuration->getParameter('role_id');
    $status_id = $this->configuration->getParameter('status_id');
    $participant_id = false;
    
    if ($this->configuration->getParameter('update_existing')) {
      // Find the participant record for this contact and event. 
      // This assumes that the contact has already been registered for the event.
      $participant_id = \CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_participant WHERE is_test = '0' AND contact_id = %1 AND event_id = %2 and role_id = %3 ORDER BY id DESC LIMIT 1", array(
        1 => array($contact_id, 'Integer'),
        2 => array($event_id, 'Integer'),
        3 => array($role_id, 'Integer')
      ));
    }
    
    // Create or Update the participant record through an API call.
    try {
      $participantParams = array();
      if ($participant_id) {
        $participantParams['id'] = $participant_id;
      }
      $participantParams['event_id'] = $event_id;
      $participantParams['status_id'] = $status_id;
      $participantParams['role_id'] = $role_id;
      $participantParams['contact_id'] = $contact_id;
      if ($this->configuration->doesParameterExists('source')) {
        $participantParams['source'] = $this->configuration->getParameter('source');
      }
      if ($parameters->getParameter('campaign_id')) {
        $participantParams['campaign_id'] = $parameters->getParameter('campaign_id');
      }
      
      foreach($this->getParameterSpecification() as $spec) {
        if (stripos($spec->getName(), 'custom_')!==0) {
          continue;
        }
        if ($parameters->doesParameterExists($spec->getName())) {
          $participantParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
        }
      }
      
      $result = civicrm_api3('Participant', 'create', $participantParams);
      $output->setParameter('id', $result['id']);
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not update or create a participant record'));
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