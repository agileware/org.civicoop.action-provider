<?php

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Parameter\OptionGroupSpecification;
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
  public static function createAddressForContact($contact_id, ParameterBagInterface $parameters, ParameterBagInterface $configuration) {
    $existingAddressId = false;
    if ($configuration->getParameter('address_update_existing')) {
      $existingAddressId = self::findExistingAddress($contact_id, $configuration->getParameter('address_location_type'), $configuration->getParameter('address_is_primary'));
      $existingAddressId = self::findMasterAddress($existingAddressId);
    }
    return self::createAddress($existingAddressId, $contact_id, $parameters, $configuration);
  }

  /**
   * Try to find an existing address for this location type
   * first see if a is_primary exists. If not then return the first one.
   *
   * @param $contact_id
   * @param $location_type_id
   *
   * @return array|bool
   */
  public static function findExistingAddress($contact_id, $location_type_id, $is_primary) {
    // First find the address with the location type and is_primary = 1
    if ($is_primary) {
      $existingAddressParams = [];
      $existingAddressParams['contact_id'] = $contact_id;
      $existingAddressParams['location_type_id'] = $location_type_id;
      $existingAddressParams['is_primary'] = 1;
      $existingAddressParams['return'] = 'id';
      try {
        return civicrm_api3('Address', 'getvalue', $existingAddressParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    $existingAddressParams = array();
    $existingAddressParams['contact_id'] = $contact_id;
    $existingAddressParams['location_type_id'] = $location_type_id;
    try {
      $result = civicrm_api3('Address', 'get', $existingAddressParams);
      foreach($result['values'] as $address) {
        return $address['id'];
      }
    } catch (\Exception $e) {
      // Do nothing
    }
    return false;
  }

  /**
   * Find the master address id.
   *
   * @param $addressId
   *
   * @return mixed
   * @throws \CiviCRM_API3_Exception
   */
  public static function findMasterAddress($addressId) {
    try {
      $master_id = civicrm_api3('Address', 'getvalue', [
        'return' => 'master_id',
        'id' => $addressId
      ]);
      if ($master_id) {
        return self::findMasterAddress($master_id);
      }
    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing
    }
    return $addressId;
  }

  /**
   * Create an address
   * @param $existingAddressId
   * @param int|null $contact_id
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $configuration
   *
   * @return bool
   * @throws \CiviCRM_API3_Exception
   */
  public static function createAddress($existingAddressId, $contact_id, ParameterBagInterface $parameters, ParameterBagInterface $configuration)  {
    // Create address
    $hasAddressParams = false;
    $addressParams = array();
    if ($existingAddressId) {
      $addressParams['id'] = $existingAddressId;
    }
    if ($contact_id && !$existingAddressId) {
      $addressParams['contact_id'] = $contact_id;
    } elseif (!$contact_id) {
      $addressParams['contact_id'] = 'null';
    }
    $addressParams['master_id'] = 'null';
    if ($configuration->doesParameterExists('address_location_type')) {
      $addressParams['location_type_id'] = $configuration->getParameter('address_location_type');
    }
    if ($configuration->doesParameterExists('address_is_primary') && $configuration->getParameter('address_is_primary')) {
      $addressParams['is_primary'] = 1;
    }
    if ($parameters->doesParameterExists('name')) {
      $addressParams['name'] = $parameters->getParameter('name');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('street_address')) {
      $addressParams['street_address'] = $parameters->getParameter('street_address');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('supplemental_address_1')) {
      $addressParams['supplemental_address_1'] = $parameters->getParameter('supplemental_address_1');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('supplemental_address_2')) {
      $addressParams['supplemental_address_2'] = $parameters->getParameter('supplemental_address_2');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('supplemental_address_3')) {
      $addressParams['supplemental_address_3'] = $parameters->getParameter('supplemental_address_3');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('street_name')) {
      $addressParams['street_name'] = $parameters->getParameter('street_name');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('street_number')) {
      $addressParams['street_number'] = $parameters->getParameter('street_number');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('street_unit')) {
      $addressParams['street_unit'] = $parameters->getParameter('street_unit');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('postal_code')) {
      $addressParams['postal_code'] = $parameters->getParameter('postal_code');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('city')) {
      $addressParams['city'] = $parameters->getParameter('city');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('state_province_id')) {
      $addressParams['state_province_id'] = $parameters->getParameter('state_province_id');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('country_id')) {
      $addressParams['country_id'] = $parameters->getParameter('country_id');
      $hasAddressParams = true;
    }
    if ($parameters->doesParameterExists('manual_geo_code')) {
      $addressParams['manual_geo_code'] = $parameters->getParameter('manual_geo_code') ? '1' : '0';
    }
    if ($parameters->doesParameterExists('latitude')) {
      $addressParams['geo_code_1'] = $parameters->getParameter('latitude');
    }
    if ($parameters->doesParameterExists('longitude')) {
      $addressParams['geo_code_2'] = $parameters->getParameter('longitude');
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
    $spec->addSpecification(new Specification('address_is_primary', 'Boolean', E::ts('Address: is primary'), false, 0, null, null, FALSE));
    $spec->addSpecification(new Specification('address_location_type', 'Integer', E::ts('Address: Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $spec->addSpecification(new Specification('address_update_existing', 'Boolean', E::ts('Address: update existing'), false, 0, null, null, FALSE));

  }

  /**
   * Update the parameter specification for create address.
   */
  public static function createAddressParameterSpecification(SpecificationBag $spec) {
    $spec->addSpecification(new Specification('name', 'String', E::ts('Address Name'), false));
    $spec->addSpecification(new Specification('street_address', 'String', E::ts('Street Address'), false));
    $spec->addSpecification(new Specification('supplemental_address_1', 'String', E::ts('Supplemental Address 1'), false));
    $spec->addSpecification(new Specification('supplemental_address_2', 'String', E::ts('Supplemental Address 2'), false));
    $spec->addSpecification(new Specification('supplemental_address_3', 'String', E::ts('Supplemental Address 3'), false));
    $spec->addSpecification(new Specification('street_name', 'String', E::ts('Street Name'), false));
    $spec->addSpecification(new Specification('street_number', 'String', E::ts('Street Number'), false));
    $spec->addSpecification(new Specification('street_unit', 'String', E::ts('Street Unit'), false));
    $spec->addSpecification(new Specification('postal_code', 'String', E::ts('Postal Code'), false));
    $spec->addSpecification(new Specification('city', 'String', E::ts('City'), false));
    $spec->addSpecification(new Specification('state_province_id', 'Integer', E::ts('State/Province'), false, null, 'StateProvince'));
    $spec->addSpecification(new Specification('country_id', 'Integer', E::ts('Country'), false, null, 'Country'));
    $spec->addSpecification(new Specification('manual_geo_code', 'Boolean', E::ts('Manual Geo Code'), false, false));
    $spec->addSpecification(new Specification('latitude', 'Float', E::ts('Latitude'), false, null, null));
    $spec->addSpecification(new Specification('longitude', 'Float', E::ts('Longitude'), false, null, null));
  }


  /**
   * Create a phone for a contact.
   */
  public static function createPhone($contact_id, ParameterBagInterface $parameters, ParameterBagInterface $configuration) {
    $existingPhoneId = false;
    if ($configuration->getParameter('phone_update_existing')) {
      $existingPhoneId = self::findExistingPhone($contact_id, $configuration->getParameter('phone_location_type'), $configuration->getParameter('phone_is_primary'));
    }

    // Create phone
    if ($parameters->getParameter('phone')) {
      $phoneParams = array();
      if ($existingPhoneId) {
        $phoneParams['id'] = $existingPhoneId;
      }
      if ($configuration->doesParameterExists('phone_is_primary') && $configuration->getParameter('phone_is_primary')) {
        $phoneParams['is_primary'] = 1;
      }
      $phoneParams['contact_id'] = $contact_id;
      $phoneParams['location_type_id'] = $configuration->getParameter('phone_location_type');
      $phoneParams['phone'] = $parameters->getParameter('phone');
      if ($configuration->getParameter('phone_phone_type')) {
        $phoneParams['phone_type_id'] = $configuration->getParameter('phone_phone_type');
      }
      $result = civicrm_api3('Phone', 'create', $phoneParams);
      return $result['id'];
    } elseif ($existingPhoneId) {
      civicrm_api3('Phone', 'delete', ['id' => $existingPhoneId]);
    }
    return false;
  }

  /**
   * Try to find an existing phone for this location type
   * first see if a is_primary exists. If not then return the first one.
   *
   * @param $contact_id
   * @param $location_type_id
   *
   * @return array|bool
   */
  public static function findExistingPhone($contact_id, $location_type_id, $is_primary) {
    // First find the phone with the location type and is_primary = 1
    if ($is_primary) {
      $existingPhoneParams = [];
      $existingPhoneParams['contact_id'] = $contact_id;
      $existingPhoneParams['location_type_id'] = $location_type_id;
      $existingPhoneParams['is_primary'] = 1;
      $existingPhoneParams['return'] = 'id';
      try {
        return civicrm_api3('Phone', 'getvalue', $existingPhoneParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    $existingPhoneParams = array();
    $existingPhoneParams['contact_id'] = $contact_id;
    $existingPhoneParams['location_type_id'] = $location_type_id;
    try {
      $result = civicrm_api3('Phone', 'get', $existingPhoneParams);
      foreach($result['values'] as $phone) {
        return $phone['id'];
      }
    } catch (\Exception $e) {
      // Do nothing
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
    $spec->addSpecification(new Specification('phone_is_primary', 'Boolean', E::ts('Phone: is primary'), false, 0, null, null, FALSE));
    $spec->addSpecification(new Specification('phone_location_type', 'Integer', E::ts('Phone: Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $spec->addSpecification(new OptionGroupSpecification('phone_phone_type', 'phone_type', E::ts('Phone: Phone type'), false));
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
      $existingEmailId = self::findExistingEmail($contact_id, $configuration->getParameter('email_location_type'), $configuration->getParameter('email_is_primary'));
    }

    if ($parameters->getParameter('email')) {
      // Create email if it does not exist yet
      if ($existingEmailId || !self::doesEmailExists($contact_id, $parameters->getParameter('email'))) {
        $emailParams = array();
        if ($existingEmailId) {
          $emailParams['id'] = $existingEmailId;
        }
        if ($configuration->doesParameterExists('email_is_primary') && $configuration->getParameter('email_is_primary')) {
          $emailParams['is_primary'] = 1;
        }
        if ($configuration->doesParameterExists('email_is_bulk') && $configuration->getParameter('email_is_bulk')) {
          $emailParams['is_bulkmail'] = 1;
        }
        $emailParams['contact_id'] = $contact_id;
        $emailParams['location_type_id'] = $configuration->getParameter('email_location_type');
        $emailParams['email'] = $parameters->getParameter('email');
        $result = civicrm_api3('Email', 'create', $emailParams);
        return $result['id'];
      }
    } elseif ($existingEmailId) {
      civicrm_api3('Email', 'delete', ['id' => $existingEmailId]);
    }
    return false;
  }

  /**
   * Try to find an existing email for this location type
   * first see if a is_primary exists. If not then return the first one.
   *
   * @param $contact_id
   * @param $location_type_id
   *
   * @return array|bool
   */
  public static function findExistingEmail($contact_id, $location_type_id, $is_primary) {
    // First find the email with the location type and is_primary = 1
    if ($is_primary) {
      $existingEmailParams = [];
      $existingEmailParams['contact_id'] = $contact_id;
      $existingEmailParams['location_type_id'] = $location_type_id;
      $existingEmailParams['is_primary'] = 1;
      $existingEmailParams['return'] = 'id';
      try {
        return civicrm_api3('Email', 'getvalue', $existingEmailParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    $existingEmailParams = array();
    $existingEmailParams['contact_id'] = $contact_id;
    $existingEmailParams['location_type_id'] = $location_type_id;
    try {
      $result = civicrm_api3('Email', 'get', $existingEmailParams);
      foreach($result['values'] as $email) {
        return $email['id'];
      }
    } catch (\Exception $e) {
      // Do nothing
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
    $spec->addSpecification(new Specification('email_is_primary', 'Boolean', E::ts('Email: is primary'), false, 0, null, null, FALSE));
    $spec->addSpecification(new Specification('email_is_bulk', 'Boolean', E::ts('Email: set is bulk mail'), false, 0, null, null, FALSE));
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

  public static function setCreatedDate($id, $created_date) {
    $year = substr($created_date, 0, 4);
    $month = substr($created_date, 4, 2);
    $day = substr($created_date, 6, 2);
    $createdDate = new \DateTime();
    $createdDate->setDate($year, $month, $day);
    \CRM_Core_DAO::executeQuery("UPDATE civicrm_contact SET created_date = %1 WHERE id = %2", array(
      1 => array($createdDate->format('Y-m-d'), 'String'),
      2 => array($id, 'Integer'),
    ));
  }

  /**
   * @param $contactTypeId
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function getContactType($contactTypeId) {
    $contactType = civicrm_api3('ContactType', 'getsingle', ['id' => $contactTypeId]);
    $contactSubType = FALSE;
    if (isset($contactType['parent_id']) && $contactType['parent_id'] > 0) {
      $contactSubType = $contactType;
      $contactType = civicrm_api3('ContactType', 'getsingle', ['id' => $contactSubType['parent_id']]);
    }
    return [
      'contact_type' => $contactType,
      'contact_sub_type' => $contactSubType,
    ];
  }

  /**
   * Method to check if the email already exists for the contact and location type
   *
   * @param $contactId
   * @param $email
   * @return bool
   */
  public static function doesEmailExists($contactId, $email) {
    if (!empty($email) && !empty($contactId)) {
      try {
        $count = civicrm_api3('Email', 'getcount', [
          'contact_id' => $contactId,
          'email' => $email,
        ]);
        if ($count > 0) {
          return TRUE;
        }
      }
      catch (\CiviCRM_API3_Exception $ex) {
      }
    }
    return FALSE;
  }

}
