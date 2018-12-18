<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateUpdateIndividual extends AbstractAction {
  
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
    // Create contact
    if ($parameters->getParameter('contact_id')) {
      $params['id'] = $parameters->getParameter('contact_id');
    }
    $contact_sub_type = $this->configuration->getParameter('contact_sub_type');
    $params['contact_type'] = "Individual";  
    if ($contact_sub_type) {
      $params['contact_sub_type'] = $contact_sub_type;
    }
    $params['first_name'] = $parameters->getParameter('first_name');
    $params['last_name'] = $parameters->getParameter('last_name');
    if ($parameters->getParameter('middle_name')) {
      $params['middle_name'] = $parameters->getParameter('middle_name');
    }
    if ($parameters->getParameter('birth_date')) {
      $params['birth_date'] = $parameters->getParameter('birth_date');
    }
    if ($parameters->getParameter('gender_id')) {
      $params['gender_id'] = $parameters->getParameter('gender_id');
    }
    $result = civicrm_api3('Contact', 'create', $params);
    $contact_id = $result['id'];
    $output->setParameter('contact_id', $contact_id);
    
    // Create address
    $address_id = ContactActionUtils::createAddressForContact($contact_id, $parameters, $this->configuration);
    if ($address_id) {
      $output->setParameter('address_id', $address_id);
    }

    // Create email
    $email_id = ContactActionUtils::createEmail($contact_id, $parameters, $this->configuration);
    if ($email_id) {
      $output->setParameter('email_id', $email_id);
    }
    
    // Create phone
    $phone_id = ContactActionUtils::createPhone($contact_id, $parameters, $this->configuration);
    if ($phone_id) {
      $output->setParameter('phone_id', $phone_id);
    }

  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $contactSubTypes = array();
    $contactSubTypesApi = civicrm_api3('ContactType', 'get', array('parent_id' => 'Individual', 'options' => array('limit' => 0)));
    $contactSubTypes[''] = E::ts(' - Select - ');
    foreach($contactSubTypesApi['values'] as $contactSubType) {
      $contactSubTypes[$contactSubType['name']] = $contactSubType['label'];
    }
  
    $spec = new SpecificationBag(array(
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), false, null, null, $contactSubTypes, FALSE),
    ));
    
    ContactActionUtils::createAddressConfigurationSpecification($spec);
    ContactActionUtils::createEmailConfigurationSpecification($spec);
    ContactActionUtils::createPhoneConfigurationSpecification($spec);
    
    return $spec;
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $contactIdSpec = new Specification('contact_id', 'Integer', E::ts('Contact ID'), false);
    $contactIdSpec->setDescription(E::ts('Leave empty to create a new Individual'));
    $spec = new SpecificationBag(array(
      $contactIdSpec,
      new Specification('first_name', 'String', E::ts('First name'), false),
      new Specification('last_name', 'String', E::ts('Last name'), false),
      new Specification('middle_name', 'String', E::ts('Middle name'), false),
      new Specification('birth_date', 'Date', E::ts('Birth date'), false),
      new OptionGroupSpecification('gender_id', 'gender', E::ts('Gender'), false),
    ));
    ContactActionUtils::createAddressParameterSpecification($spec);
    ContactActionUtils::createEmailParameterSpecification($spec);
    ContactActionUtils::createPhoneParameterSpecification($spec);
    return $spec;
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
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), false),
      new Specification('address_id', 'Integer', E::ts('Address record ID'), false),
      new Specification('email_id', 'Integer', E::ts('Email record ID'), false),
      new Specification('phone_id', 'Integer', E::ts('Phone ID'), false),
    ));
  }
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Create or update Individual'); 
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
