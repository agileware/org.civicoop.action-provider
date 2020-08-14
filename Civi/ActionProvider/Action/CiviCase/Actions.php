<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

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
    $provider->addAction('GetCaseDataById', '\Civi\ActionProvider\Action\CiviCase\GetCaseDataById', E::ts('CiviCase: Get by ID'), array(
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('FindContactWithRoleOnCase', '\Civi\ActionProvider\Action\CiviCase\FindContactWithRoleOnCase', E::ts('CiviCase: Get contact with a role on the case'), array(
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('CreateCase', '\Civi\ActionProvider\Action\CiviCase\CreateCase', E::ts('CiviCase: Create'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('CaseUpdateCustomData', '\Civi\ActionProvider\Action\CiviCase\UpdateCustomData',E::ts('CiviCase: Update custom data') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('CaseUploadCustomFileField', '\Civi\ActionProvider\Action\CiviCase\UploadCustomFileField',E::ts('CiviCase: Upload file to a custom field for a case') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
  }

}