<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Provider;
use CRM_ActionProvider_ExtensionUtil as E;

class Actions {

  /**
   * Load activity actions
   *
   * @param \Civi\ActionProvider\Provider $provider
   */
  public static function loadActions(Provider $provider) {
    $provider->addAction('ContactDataById', '\Civi\ActionProvider\Action\Contact\ContactDataById', E::ts('Contact: Get by ID'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('CreateUpdateAddress', '\Civi\ActionProvider\Action\Contact\CreateUpdateAddress', E::ts('Contact: Create or update address of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateUpdateEmailAddress', '\Civi\ActionProvider\Action\Contact\CreateUpdateEmailAddress', E::ts('Contact: Create or update an e-mail address of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UsePrimaryAddressOfContact', '\Civi\ActionProvider\Action\Contact\UsePrimaryAddressOfContact', E::ts('Contact: Use primary address of another contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UsePrimaryAddressOfRelatedContact', '\Civi\ActionProvider\Action\Contact\UsePrimaryAddressOfRelatedContact', E::ts('Contact: Use primary address of related contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetAddress', '\Civi\ActionProvider\Action\Contact\GetAddress', E::ts('Address: Get by ID'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetEmailAddress', '\Civi\ActionProvider\Action\Contact\GetEmailAddress', E::ts('Contact: Get e-mail address'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetStateProvinceId', '\Civi\ActionProvider\Action\Contact\GetStateProvinceId', E::ts('Address: Get state/province ID by name'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetCountryId', '\Civi\ActionProvider\Action\Contact\GetCountryId', E::ts('Address: Get country ID by name/ISO code'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetAddressById', '\Civi\ActionProvider\Action\Contact\GetAddressById', E::ts('Address: Get address by ID'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetContactIdFromMasterAddress', '\Civi\ActionProvider\Action\Contact\GetContactIdFromMasterAddress', E::ts('Contact: Get contact ID of a master address'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetLoggedInContactId', '\Civi\ActionProvider\Action\Contact\GetCurrentUserContactID', E::ts('Contact: Get contact ID of the currently logged in user'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FindIndividualByNameAndEmail', '\Civi\ActionProvider\Action\Contact\FindIndividualByNameAndEmail', E::ts('Contact: Get Individual by name and email'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FindByExternalId', '\Civi\ActionProvider\Action\Contact\FindByExternalId', E::ts('Contact: Get by external id'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FindContactByCustomField', '\Civi\ActionProvider\Action\Contact\FindByCustomField', E::ts('Contact: Get contact by custom field'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FindOrganizationByName', '\Civi\ActionProvider\Action\Contact\FindOrganizationByName', E::ts('Contact: Get organization by name'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FindOrCreateOrganizationByName', '\Civi\ActionProvider\Action\Contact\FindOrCreateOrganizationByName', E::ts('Contact: Get or create organization by name'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('CreateUpdateIndividual', '\Civi\ActionProvider\Action\Contact\CreateUpdateIndividual', E::ts('Contact: Create or update Individual'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateUpdateHousehold', '\Civi\ActionProvider\Action\Contact\CreateUpdateHousehold', E::ts('Contact: Create or update Household'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateUpdateOrganization', '\Civi\ActionProvider\Action\Contact\CreateUpdateOrganization', E::ts('Contact: Create or update Organization'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UpdateCustomData', '\Civi\ActionProvider\Action\Contact\UpdateCustomData', E::ts('Contact: Update custom data'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ContactCreateNote', '\Civi\ActionProvider\Action\Contact\CreateNote', E::ts('Contact: Create Note'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('MarkContactAsDeceased', '\Civi\ActionProvider\Action\Contact\MarkContactAsDeceased', E::ts('Contact: Mark as deceased'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('SetContactSubType', '\Civi\ActionProvider\Action\Contact\SetContactSubType', E::ts('Contact: Set subtype'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('RemoveContactSubType', '\Civi\ActionProvider\Action\Contact\RemoveContactSubType', E::ts('Contact: Remove subtype'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ContactHasSubType', '\Civi\ActionProvider\Action\Contact\ContactHasSubType', E::ts('Contact: Has subtype'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FormatIndividualName', '\Civi\ActionProvider\Action\Contact\FormatIndividualName', E::ts('Contact: Format Individual Name'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('SetContactEmployer', '\Civi\ActionProvider\Action\Contact\SetEmployer', E::ts('Contact: Set employer'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UploadCustomFileField', '\Civi\ActionProvider\Action\Contact\UploadCustomFileField', E::ts('Contact: Upload file to a custom field for a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('SetCommunicationStyle', '\Civi\ActionProvider\Action\Contact\SetCommunicationStyle', E::ts("Contact: Edit communication styles"), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('SetPreferredCommunicationMethod', '\Civi\ActionProvider\Action\Contact\SetPreferredCommunicationMethod', E::ts("Contact: Edit preferred communication methods"), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('FindOrCreateContactByEmail', '\Civi\ActionProvider\Action\Contact\FindOrCreateContactByEmail', E::ts('Contact: Get or create contact by e-mail'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('FindContactByEmail', '\Civi\ActionProvider\Action\Contact\FindContactByEmail', E::ts('Contact: Get contact by e-mail'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
    ));
    $provider->addAction('ValidateChecksum', '\Civi\ActionProvider\Action\Contact\ValidateChecksum', E::ts('Contact: Validate checksum'), [
      AbstractAction::DATA_RETRIEVAL_TAG, AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('GenerateChecksum', '\Civi\ActionProvider\Action\Contact\GenerateChecksum', E::ts('Contact: Generate checksum'), [
      AbstractAction::DATA_RETRIEVAL_TAG, AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('RestoreContactFromContact', '\Civi\ActionProvider\Action\Contact\RestoreContactFromTrash', E::ts('Contact: Restore from trash'), [
      AbstractAction::DATA_MANIPULATION_TAG, AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('BlockCommunication', '\Civi\ActionProvider\Action\Contact\BlockCommunication', E::ts('Contact: Block Communication (set all do not fields)'), [
      AbstractAction::DATA_MANIPULATION_TAG, AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('SaveMaxContactId', '\Civi\ActionProvider\Action\Contact\SaveMaxContactId', E::ts('Contact: Get maximum contact ID'), [
      AbstractAction::DATA_RETRIEVAL_TAG
    ]);
    $provider->addAction('FindByEmailOrCreateEmailName', '\Civi\ActionProvider\Action\Contact\FindByEmailOrCreateEmailName', E::ts('Contact: Get By Email or Create By Email and Names'), [
      AbstractAction::DATA_RETRIEVAL_TAG, AbstractAction::DATA_MANIPULATION_TAG
    ]);
    $provider->addAction('FindOrCreateContactByEmailAndName', '\Civi\ActionProvider\Action\Contact\FindOrCreateContactByEmailAndName', E::ts('Contact: Get or Create By Email and Names'), [
      AbstractAction::DATA_RETRIEVAL_TAG, AbstractAction::DATA_MANIPULATION_TAG
    ]);
    $provider->addAction('CreateUpdateWebsite', '\Civi\ActionProvider\Action\Website\CreateUpdateWebsite', E::ts('Contact: Create or update website'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetWebsite', '\Civi\ActionProvider\Action\Website\GetWebsite', E::ts('Contact: Get website'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('CreateUpdatePhone', '\Civi\ActionProvider\Action\Phone\CreateUpdatePhone', E::ts('Contact: Create or update phone number'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetPhone', '\Civi\ActionProvider\Action\Phone\GetPhone', E::ts('Contact: Get phone number'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FormatPhoneNumeric', '\Civi\ActionProvider\Action\Phone\FormatPhoneNumeric', E::ts('Contact: Format Phone into Numbers Only'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetEmployer', '\Civi\ActionProvider\Action\Contact\GetEmployer', E::ts('Contact: Get employer'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('AddressComponentIdLookup', '\Civi\ActionProvider\Action\Contact\AddressComponentIdLookup', E::ts('Address: Get country or state/province from ID'), array(
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('FindByCustomGroupMultiValue', '\Civi\ActionProvider\Action\Contact\FindByCustomGroupMultiValue', E::ts('Contact: Find entry of multi valued custom group'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetCustomGroupMultiValueById', '\Civi\ActionProvider\Action\Contact\GetCustomGroupMultiValueById', E::ts('Contact: Get entry of multi valued custom group'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('UpdateCustomGroupMultiValue', '\Civi\ActionProvider\Action\Contact\UpdateCustomGroupMultiValue', E::ts('Contact: Update entry of multi valued custom group'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));

  }

}