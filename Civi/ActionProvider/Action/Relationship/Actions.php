<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Relationship;

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
    $provider->addAction('GetRelationshipTypeIdByName', '\Civi\ActionProvider\Action\Relationship\GetRelationshipTypeIdByName',E::ts('Relationship: Get type ID by name') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetRelationship', '\Civi\ActionProvider\Action\Relationship\GetRelationship',E::ts('Relationship: Get by contact A and B') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetRelationshipById', '\Civi\ActionProvider\Action\Relationship\GetRelationshipById',E::ts('Relationship: Get by ID') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetRelationshipByContactId', '\Civi\ActionProvider\Action\Relationship\GetRelationshipByContactId',E::ts('Relationship: Get by Contact ID') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('CreateRelationship', '\Civi\ActionProvider\Action\Relationship\CreateRelationship',E::ts('Relationship: Create') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateOrUpdateRelationship', '\Civi\ActionProvider\Action\Relationship\CreateOrUpdateRelationship',E::ts('Relationship: Create or update') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UpdateRelationshipById', '\Civi\ActionProvider\Action\Relationship\UpdateRelationshipById',E::ts('Relationship: Update by ID') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateRelationshipWithTypeParameter', '\Civi\ActionProvider\Action\Relationship\CreateRelationshipWithTypeParameter',E::ts('Relationship: Create (with relationship type parameter)') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('EndRelationship', '\Civi\ActionProvider\Action\Relationship\EndRelationship',E::ts('Relationship: End') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('RelationshipValidateChecksums', '\Civi\ActionProvider\Action\Relationship\ValidateChecksum', E::ts('Relationship: Validate checksums'), [
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG
    ]);
    $provider->addAction('CreateRelationshipWithMultipleContacts', '\Civi\ActionProvider\Action\Relationship\CreateRelationshipWithMultipleContacts', E::ts('Relationship: Create with one or more contacts'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG
    ]);
  }

}
