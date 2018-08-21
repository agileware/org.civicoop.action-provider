<?php

namespace Civi\ActionProvider\Action\Phone;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateUpdatePhone extends AbstractAction {
  
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
    $existingPhoneId = false;
    $contact_id = $parameters->getParameter('contact_id');
    $existingPhoneParams['contact_id'] = $contact_id;
    $existingPhoneParams['phone_type_id'] = $this->configuration->getParameter('phone_type');
    $existingPhoneParams['location_type_id'] = $this->configuration->getParameter('location_type');
    $existingPhoneParams['return'] = 'id';
    try {
      $existingPhoneId = civicrm_api3('Phone', 'getvalue', $existingPhoneParams);
    } catch (\Exception $e) {
      // Do nothing
    }
    
    if (!empty($parameters->getParameter('phone'))) {
      $params = array();
      if ($existingPhoneId) {
        $params['id'] = $existingPhoneId;
      }
      $params['phone_type_id'] = $this->configuration->getParameter('phone_type');
      $params['location_type_id'] = $this->configuration->getParameter('location_type');
      $params['contact_id'] = $contact_id;
      $params['phone'] = $parameters->getParameter('phone');
      civicrm_api3('Phone', 'create', $params);
    } elseif ($existingPhoneId) {
      $params = array();
      $params['id'] = $existingPhoneId;
      civicrm_api3('Phone', 'delete', $params);
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
      new OptionGroupSpecification('phone_type', 'phone_type', E::ts('Phone Type'), true),
      new Specification('location_type', 'Integer', E::ts('Location type'), true, $defaultLocationType, null, $locationTypes, FALSE),
    ));
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), false),
      new Specification('phone', 'String', E::ts('Phone'), false),
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
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Create or update phonenumber of a contact');
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