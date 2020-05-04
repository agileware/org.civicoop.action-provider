<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateParticipantById extends AbstractAction {

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
    $fieldsToIgnore = ['id', 'event_id', 'contact_id'];

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
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true, null, null, null, FALSE),
    ));

    $fields = civicrm_api3('Participant', 'getfields', array('action' => 'get', 'options' => array('limit' => 0)));
    foreach($fields['values'] as $field) {
      if (in_array($field['name'], $fieldsToIgnore)) {
        continue;
      }
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
      $specs->addSpecification($fieldSpec);
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
    $participant_id = $parameters->getParameter('participant_id');
    // Create or Update the participant record through an API call.
    try {
      $participantParams = array();
      if ($participant_id) {
        $participantParams['id'] = $participant_id;
      }
      foreach($this->getParameterSpecification() as $spec) {
        if ($parameters->doesParameterExists($spec->getName())) {
          $participantParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
        }
      }

      $result = civicrm_api3('Participant', 'create', $participantParams);
      $output->setParameter('id', $result['id']);
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not update the participant record'));
    }
  }

}
