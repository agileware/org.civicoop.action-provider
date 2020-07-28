<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

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
    $provider->addAction('CreateActivity', '\Civi\ActionProvider\Action\Activity\CreateActivity',E::ts('Activity: Create/Update') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG
    ));
    $provider->addAction('UpdateActivityStatus', '\Civi\ActionProvider\Action\Activity\UpdateActivityStatus',E::ts('Activity: Update status') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $provider->addAction('DeleteActivity', '\Civi\ActionProvider\Action\Activity\DeleteActivity', E::ts('Activity: Delete'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG
    ));
    $provider->addAction('GetActivity', '\Civi\ActionProvider\Action\Activity\GetActivity', E::ts('Activity: Get activity by ID'), array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetActivityContact', '\Civi\ActionProvider\Action\Activity\GetActivityContact', E::ts('Activity: Get contact IDs from an activity'), array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetMostRecentActivity', '\Civi\ActionProvider\Action\Activity\GetMostRecentActivity', E::ts('Activity: Get most recent activity of a contact'), array(
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
  }

}
