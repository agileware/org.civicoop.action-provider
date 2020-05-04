<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetParticipantById extends AbstractAction {

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
    $participant_id = $parameters->getParameter('participant_id');
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


}
