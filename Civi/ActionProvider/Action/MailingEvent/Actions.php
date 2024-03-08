<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\MailingEvent;

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
    $provider->addAction('MailingEventSubscribe', '\Civi\ActionProvider\Action\MailingEvent\MailingEventSubscribe', E::ts('Mailing Event: Subscribe to mailing list'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
    ));
    $provider->addAction('MailingEventUnsubscribe', '\Civi\ActionProvider\Action\MailingEvent\MailingEventUnsubscribe', E::ts('Mailing Event: Unsubscribe from mailing list'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
    ));
    $provider->addAction('MailingEventConfirm', '\Civi\ActionProvider\Action\MailingEvent\MailingEventConfirm', E::ts('Mailing Event: Confirm mailing list subscription'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
    ));
    $provider->addAction('MailingEventGetContactId', '\Civi\ActionProvider\Action\MailingEvent\MailingEventGetContactId', E::ts('Mailing Event: Get Contact ID from Mailing'), array(
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
    ));
  }

}
