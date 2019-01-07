<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetParticipant extends AbstractAction {
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Get participant data'); 
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new OptionGroupSpecification('role_id', 'participant_role', E::ts('Role'), true, null, FALSE),
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
    ));
    
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
    $bag = new SpecificationBag();
    $contact_fields = civicrm_api3('Participant', 'getfields', array('action' => 'get', 'options' => array('limit' => 0)));
    foreach($contact_fields['values'] as $field) {
      if (empty($field['type'])) {
        continue;
      }
      $type = \CRM_Utils_Type::typeToString($field['type']);
      if (empty($type)) {
        continue;
      }
      switch ($type) {
        case 'Int':
          $type = 'Integer';
          break;
      }
      if (stripos($field['name'], 'custom_') === 0) {
        $fieldId = substr($field['name'], 7);
        $name = CustomField::getCustomFieldName($fieldId);
        $title = $field['groupTitle'].' :: '.$field['title'];
        $fieldSpec = new Specification($name, $type, $title, FALSE);
        $fieldSpec->setApiFieldName($field['name']);
      } else {
        $fieldSpec = new Specification(
          $field['name'],
          $type,
          $field['title'],
          FALSE
        );
        $fieldSpec->setApiFieldName($field['name']);
      }
      $bag->addSpecification($fieldSpec);
    }
    
    return $bag;
  }
  
  /**
   * Run the action
   * 
   * @param ParameterBagInterface $parameters
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
    
    $participant_id = \CRM_Core_DAO::singleValueQuery("SELECT id FROM civicrm_participant WHERE is_test = '0' AND contact_id = %1 AND event_id = %2 and role_id = %3 ORDER BY id DESC LIMIT 1", array(
      1 => array($contact_id, 'Integer'),
      2 => array($event_id, 'Integer'),
      3 => array($role_id, 'Integer')
    ));
    
    if (!$participant_id) {
      return;
    }
    
    try {
      $participant = civicrm_api3('Participant', 'getsingle', array('id' => $participant_id));
      foreach($this->getOutputSpecification() as $spec) {
        if (isset($participant[$spec->getApiFieldName()])) {
          $output->setParameter($spec->getName(), $participant[$spec->getApiFieldName()]);
        }
      }
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    );
  }

}
