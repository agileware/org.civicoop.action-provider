<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Group;

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
    $provider->addAction('AddToGroup', '\Civi\ActionProvider\Action\Group\AddToGroup', E::ts('Contact: Add to Group'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('AddToGroupParameter', '\Civi\ActionProvider\Action\Group\AddToGroupParameter', E::ts('Contact: Add to Group (with group ID as parameter)'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('RemoveFromGroupParameter', '\Civi\ActionProvider\Action\Group\RemoveFromGroupParameter', E::ts('Contact: Remove from  group (with group ID as parameter)'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateGroup', '\Civi\ActionProvider\Action\Group\Create', E::ts('Group: Create or update'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      'group',
    ));
    $provider->addAction('GetGroup', '\Civi\ActionProvider\Action\Group\GetGroup', E::ts('Group: Get by ID'), array(
      AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('DeleteGroup', '\Civi\ActionProvider\Action\Group\DeleteGroup', E::ts('Group: Delete'), array(
      AbstractAction::DATA_MANIPULATION_TAG
    ));
  }

}
