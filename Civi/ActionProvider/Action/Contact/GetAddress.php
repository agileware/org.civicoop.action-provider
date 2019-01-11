<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetAddress extends AbstractAction {
  
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
    $contact_id = $parameters->getParameter('contact_id');
    $existingAddressParams['contact_id'] = $contact_id;
    $existingAddressParams['location_type_id'] = $this->configuration->getParameter('location_type_id');
    try {
      $existingAddress = civicrm_api3('Address', 'getsingle', $existingAddressParams);
      foreach($existingAddress as $field => $value) {
        $output->setParameter($field, $value);
      }
      
    } catch (\Exception $e) {
      // Do nothing
    }
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $locationTypes = ContactActionUtils::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    return new SpecificationBag(array(
      new Specification('location_type_id', 'Integer', E::ts('Location type'), true, $defaultLocationType, null, $locationTypes, FALSE)
    ));
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
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
    $bag = new SpecificationBag();
    $contact_fields = civicrm_api3('address', 'getfields', array('action' => 'get', 'options' => array('limit' => 0)));
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
        // It is a custom field
        $customFieldId = str_replace("custom_", "", $field['name']);
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
      $bag->addSpecification($fieldSpec);
    }
    
    return $bag;
  }
  
}