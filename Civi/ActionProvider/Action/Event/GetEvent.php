<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use CRM_ActionProvider_ExtensionUtil as E;

class GetEvent extends AbstractAction {
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Get event data');
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
      new Specification('event_id', 'Integer', E::ts('Event ID'), false, null, null, null, FALSE),
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
    $event_fields = civicrm_api3('Event', 'getfields', array('action' => 'get', 'options' => array('limit' => 0)));
    foreach($event_fields['values'] as $field) {
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
        $customFieldId = str_replace("custom_", "", $field['name']);
        /*var_dump($field);
        // It is a custom field
        $customFieldId = str_replace("custom_", "", $field['name']);
        $fieldSpec = CustomField::getSpecFromCustomField($field);
        echo $field['id']."\r\n";
        if ($fieldSpec) {
          echo "API: ".$fieldSpec->getApiFieldName()."\r\n";
        }*/
        $fieldName = CustomField::getCustomFieldName($customFieldId);
        $fieldSpec = new Specification(
          $fieldName,
          $type,
          $field['title'],
          false
        );
        $fieldSpec->setApiFieldName($field['name']);
      } else {
        $fieldSpec = new Specification(
          $field['name'],
          $type,
          $field['title'],
          false
        );
      }
      if ($fieldSpec) {
        $bag->addSpecification($fieldSpec);
      }
    }

    $bag->addSpecification(new Specification('address_id', 'Integer', E::ts('Address ID')));

    return $bag;
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
    $event_id = $parameters->getParameter('event_id');

    if (!$event_id) {
      return;
    }

    try {
      $event = civicrm_api3('Event', 'getsingle', array('id' => $event_id));

      foreach($this->getOutputSpecification() as $spec) {
        $fieldName = $spec->getName();
        if (stripos($fieldName, 'custom_') === 0) {
          $fieldName = $spec->getApiFieldName();
        }
        if (stripos($fieldName, 'custom_') === 0 && isset($event[$fieldName.'_id'])) {
          $fieldName = $fieldName . '_id';
        }
        if (isset($event[$fieldName])) {
          $output->setParameter($spec->getName(), $event[$fieldName]);
        }
      }

      // Get Address ID
      try {
        $event = civicrm_api3('Event', 'getsingle', ['id' => $parameters->getParameter('event_id')]);
        $locationInUseByOtherEvents = civicrm_api3('Event', 'getcount', array('loc_block_id' => $event['loc_block_id']));
        if ($locationInUseByOtherEvents == 1) {
          $loc = civicrm_api3('LocBlock', 'getsingle', ['id' => $event['loc_block_id']]);
          $output->setParameter('address_id', $loc['address_id']);
        }
      } catch (\Exception $e) {
        // Do nothing
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
      AbstractAction::DATA_RETRIEVAL_TAG,
    );
  }
  
}