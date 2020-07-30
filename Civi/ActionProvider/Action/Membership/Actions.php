<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Membership;

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
    $provider->addAction('CreateOrUpdateMembership', '\Civi\ActionProvider\Action\Membership\CreateOrUpdateMembership',E::ts('Membership: Create or update') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('CreateOrUpdateMembershipWithTypeParameter', '\Civi\ActionProvider\Action\Membership\CreateOrUpdateMembershipWithTypeParameter',E::ts('Membership: Create or update (with type as parameter)') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UpdateMembership', '\Civi\ActionProvider\Action\Membership\UpdateMembership',E::ts('Membership: Update') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('OverrideMembershipStatus', '\Civi\ActionProvider\Action\Membership\OverrideStatus',E::ts('Membership: Override Status') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetMembershipById', '\Civi\ActionProvider\Action\Membership\GetMembershipById',E::ts('Membership: Get by ID') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetMembershipType', '\Civi\ActionProvider\Action\Membership\GetMembershipType',E::ts('Membership: Get type') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetMembershipStatus', '\Civi\ActionProvider\Action\Membership\GetMembershipStatus',E::ts('Membership: Get status') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $provider->addAction('GetMembershipTypeByOrganization', '\Civi\ActionProvider\Action\Membership\GetMembershipTypeByOrganization',E::ts('Membership: Get type by organization') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
  }

}
