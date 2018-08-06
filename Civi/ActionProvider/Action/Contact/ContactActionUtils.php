<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * This is a helper class for contact create/update functions.
 * The following functions are available in this class:
 *  - create/update email
 *  - create/update address
 *  - create/update phone
 */ 
class ContactActionUtils {
  
  private static $locationTypes = false;
  
  /**
   * Create an address for a contact.
   */
  public static function createAddress($contact_id, ParameterBagInterface $parameters, ParameterBagInterface $configuration) {
    $existingAddressId = false;  
    if ($configuration->getParameter('address_update_existing')) {
      // Try to find existing address
      $existingAddressParams['contact_id'] = $contact_id;
      $existingAddressParams['location_type_id'] = $configuration->getParameter('address_location_type');
      $existingAddressParams['return'] = 'id';
      try {
        $existingAddressId = civicrm_api3('Address', 'getvalue', $existingAddressParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    
    // Create address
    $hasAddressParams = false;
    $addressParams = array();
    if ($existingAddressId) {
      $addressParams['id'] = $existingAddressId;
    }
    $addressParams['contact_id'] = $contact_id;
    $addressParams['master_id'] = 'null';
    $addressParams['location_type_id'] = $configuration->getParameter('address_location_type');
    if ($parameters->getParameter('name')) {
      $addressParams['name'] = $parameters->getParameter('name');
      $hasAddressParams = true;
    }
    if ($parameters->getParameter('supplemental_address_1')) {
      $addressParams['supplemental_address_1'] = $parameters->getParameter('supplemental_address_1');
      $hasAddressParams = true;
    }
    if ($parameters->getParameter('street_address')) {
      $addressParams['street_address'] = $parameters->getParameter('street_address');
      $hasAddressParams = true;
    }
    if ($parameters->getParameter('postal_code')) {
      $addressParams['postal_code'] = $parameters->getParameter('postal_code');
      $hasAddressParams = true;
    }
    if ($parameters->getParameter('city')) {
      $addressParams['city'] = $parameters->getParameter('city');
      $hasAddressParams = true;
    }
    if ($parameters->getParameter('country_id')) {
      $addressParams['country_id'] = $parameters->getParameter('country_id');
      $hasAddressParams = true;
    }
    if ($hasAddressParams) {
      $result = civicrm_api3('Address', 'create', $addressParams);
      return $result['id'];
    }    
    
    return false;
  }
  
  /**
   * Update the configuration specification for create address.
   */
  public static function createAddressConfigurationSpecification(SpecificationBag $spec) {
    $locationTypes = self::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    $spec->addSpecification(new Specification('address_location_type', 'Integer', E::ts('Address: Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $spec->addSpecification(new Specification('address_update_existing', 'Boolean', E::ts('Address: update existing'), false, 0, null, null, FALSE));
  }
  
  /**
   * Update the parameter specification for create address.
   */
  public static function createAddressParameterSpecification(SpecificationBag $spec) {
    $spec->addSpecification(new Specification('name', 'String', E::ts('Address name'), false));
    $spec->addSpecification(new Specification('supplemental_address_1', 'String', E::ts('Supplemental address 1'), false));
    $spec->addSpecification(new Specification('street_address', 'String', E::ts('Street and housenumber'), false));
    $spec->addSpecification(new Specification('postal_code', 'String', E::ts('Postal code'), false));
    $spec->addSpecification(new Specification('city', 'String', E::ts('City'), false));
    $spec->addSpecification(new Specification('country_id', 'Integer', E::ts('Country ID'), false));
  }
  
  
  /**
   * Create a phone for a contact.
   */
  public static function createPhone($contact_id, ParameterBagInterface $parameters, ParameterBagInterface $configuration) {
    $existingPhoneId = false;  
    if ($configuration->getParameter('phone_update_existing')) {
      // Try to find existing phone number
      $existingPhoneParams['contact_id'] = $contact_id;
      $existingPhoneParams['location_type_id'] = $configuration->getParameter('phone_location_type');
      $existingPhoneParams['return'] = 'id';
      try {
        $existingPhoneId = civicrm_api3('Phone', 'getvalue', $existingPhoneParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    
    // Create phone
    if ($parameters->getParameter('phone')) {
      $phoneParams = array();
      if ($existingPhoneId) {
        $phoneParams['id'] = $existingPhoneId;
      }
      $phoneParams['contact_id'] = $contact_id;
      $phoneParams['location_type_id'] = $configuration->getParameter('phone_location_type');
      $phoneParams['phone'] = $parameters->getParameter('phone');
      $result = civicrm_api3('Phone', 'create', $phoneParams);
      return $result['id'];
    }
    return false;
  }
  
  /**
   * Update the configuration specification for create phone.
   */
  public static function createPhoneConfigurationSpecification(SpecificationBag $spec) {
    $locationTypes = self::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    $spec->addSpecification(new Specification('phone_location_type', 'Integer', E::ts('Phone: Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $spec->addSpecification(new Specification('phone_update_existing', 'Boolean', E::ts('Phone: update existing'), false, 0, null, null, FALSE));
  }
  
  /**
   * Update the parameter specification for create phone.
   */
  public static function createPhoneParameterSpecification(SpecificationBag $spec) {
    $spec->addSpecification(new Specification('phone', 'String', E::ts('Phonenumber'), false));
  }
  
  
  /**
   * Create an e-mail address for a contact.
   */
  public static function createEmail($contact_id, ParameterBagInterface $parameters, ParameterBagInterface $configuration) {
    $existingEmailId = false;  
    if ($configuration->getParameter('email_update_existing')) {
      // Try to find existing email address
      $existingEmailParams['contact_id'] = $contact_id;
      $existingEmailParams['location_type_id'] = $configuration->getParameter('email_location_type');
      $existingEmailParams['return'] = 'id';
      try {
        $existingEmailId = civicrm_api3('Email', 'getvalue', $existingEmailParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    
    // Create email
    if ($parameters->getParameter('email')) {
      $emailParams = array();
      if ($existingEmailId) {
        $emailParams['id'] = $existingEmailId;
      }
      $emailParams['contact_id'] = $contact_id;
      $emailParams['location_type_id'] = $configuration->getParameter('email_location_type');
      $emailParams['email'] = $parameters->getParameter('email');
      $result = civicrm_api3('Email', 'create', $emailParams);
      return $result['id'];
    }
    return false;
  }
  
  /**
   * Update the configuration specification for create email.
   */
  public static function createEmailConfigurationSpecification(SpecificationBag $spec) {
    $locationTypes = self::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    $spec->addSpecification(new Specification('email_location_type', 'Integer', E::ts('E-mail: Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $spec->addSpecification(new Specification('email_update_existing', 'Boolean', E::ts('E-mail: update existing'), false, 0, null, null, FALSE));
  }
  
  /**
   * Update the parameter specification for create email.
   */
  public static function createEmailParameterSpecification(SpecificationBag $spec) {
    $spec->addSpecification(new Specification('email', 'String', E::ts('E-mail'), false));
  }
  
  /**
   * Returns the location types
   */
  public static function getLocationTypes() {
    if (!self::$locationTypes) {
      self::$locationTypes = array();
      $locationTypesApi = civicrm_api3('LocationType', 'get', array('options' => array('limit' => 0)));
      foreach($locationTypesApi['values'] as $locationType) {
        self::$locationTypes[$locationType['id']] = $locationType['display_name'];
      }
    }
    return self::$locationTypes;
  }
  
}
