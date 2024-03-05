<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Communication;

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
    $provider->addAction('MessageTemplateByName', '\Civi\ActionProvider\Action\Communication\MessageTemplateByName', E::ts('Communication: Get message template by name'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('MessageTemplateById', '\Civi\ActionProvider\Action\Communication\MessageTemplateById', E::ts('Communication: Get message template by ID'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('SendEmail', '\Civi\ActionProvider\Action\Communication\SendEmail', E::ts('Communication: Send e-mail'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS
    ));
    $provider->addAction('CreatePdf', '\Civi\ActionProvider\Action\Communication\CreatePdf', E::ts('Communication: Create PDF'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS
    ));
    $provider->addAction('SendPdfByEmail', '\Civi\ActionProvider\Action\Communication\SendPdfByEmail', E::ts('Communication: Create PDF and send by e-mail'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS
    ));
    $provider->addAction('SendEmailToGroup', '\Civi\ActionProvider\Action\Communication\SendEmailToGroup', E::ts('Communication: Send e-mail to group' ), array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS
    ));
  }

}
