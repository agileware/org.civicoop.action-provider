<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Campaign;

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
    $provider->addAction('FindOrCreateCampaign', '\Civi\ActionProvider\Action\Campaign\FindOrCreateCampaign', E::ts('Campaign: Get or create'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('UpdateCampaign', '\Civi\ActionProvider\Action\Campaign\UpdateCampaign', E::ts('Campaign: Update'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('GetCampaign', '\Civi\ActionProvider\Action\Campaign\GetCampaign', E::ts('Campaign: Get by ID'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
  }

}
