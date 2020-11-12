<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Event;

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
    $provider->addAction('UpdateParticipantStatus', '\Civi\ActionProvider\Action\Event\UpdateParticipantStatus',E::ts('Participant: Update status') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UpdateParticipantStatusWithDynamicStatus', '\Civi\ActionProvider\Action\Event\UpdateParticipantStatusWithDynamicStatus',E::ts('Participant: Update status given by a parameter') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateOrUpdateParticipant', '\Civi\ActionProvider\Action\Event\CreateOrUpdateParticipant',E::ts('Participant: Register contact for an event') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateOrUpdateParticipantWithDynamicStatus', '\Civi\ActionProvider\Action\Event\CreateOrUpdateParticipantWithDynamicStatus', E::ts('Participant: Register contact for an event (with status as parameter)') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateOrUpdateParticipantWithDynamicStatusRole', '\Civi\ActionProvider\Action\Event\CreateOrUpdateParticipantWithDynamicStatusRole', E::ts('Participant: Register contact for an event (with status and role as parameter)') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateOrUpdateEvent', '\Civi\ActionProvider\Action\Event\CreateOrUpdateEvent', E::ts('Event: Create or update') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateRecurringEvent', '\Civi\ActionProvider\Action\Event\CreateRecurringEvent', E::ts('Event: Repeat') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetRecurringEvent', '\Civi\ActionProvider\Action\Event\GetRecurringEvent', E::ts('Event: Get repetition') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetEvent', '\Civi\ActionProvider\Action\Event\GetEvent',E::ts('Event: Get by ID') , array(
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('EventUploadCustomFileField', '\Civi\ActionProvider\Action\Event\UploadCustomFileField',E::ts('Event: Upload file to a custom field for an event') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('DeleteEvent', '\Civi\ActionProvider\Action\Event\DeleteEvent',E::ts('Event: Delete') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetParticipant', '\Civi\ActionProvider\Action\Event\GetParticipant',E::ts('Participant: Get by contact ID and event ID') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetParticipantById', '\Civi\ActionProvider\Action\Event\GetParticipantById',E::ts('Participant: Get by ID') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('UpdateParticipantById', '\Civi\ActionProvider\Action\Event\UpdateParticipantById',E::ts('Participant: Update by ID') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('DeleteParticipant', '\Civi\ActionProvider\Action\Event\DeleteParticipant', E::ts('Participant: Delete'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('SendParticipantRegistrationMail', '\Civi\ActionProvider\Action\Event\SendParticipantRegistrationMail', E::ts('Participant: Send Registration Confirmation'), array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('SendEmailToParticipants', '\Civi\ActionProvider\Action\Event\SendEmailToParticipants', E::ts('Participant: Send e-mail to participants'), array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
    ));
    $provider->addAction('UpdateEventStatus', '\Civi\ActionProvider\Action\Event\UpdateEventStatus', E::ts('Event: Update is public and is active state') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
  }

}
