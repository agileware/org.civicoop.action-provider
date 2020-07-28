<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\BulkMail;

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
    $provider->addAction('AddAttachmentToBulkMail', '\Civi\ActionProvider\Action\BulkMail\AddAttachment', E::ts('Mailing: Add attachment'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('Send', '\Civi\ActionProvider\Action\BulkMail\Send',E::ts('Mailing: Create and send mass mailing') , array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
      'bulk_mail'
    ));
  }

}
