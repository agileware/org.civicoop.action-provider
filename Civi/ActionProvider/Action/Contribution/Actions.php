<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

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
    $provider->addAction('GetContribution', '\Civi\ActionProvider\Action\Contribution\GetContribution', E::ts('Contribution: Get data'), array(
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('CreateContribution', '\Civi\ActionProvider\Action\Contribution\CreateContribution', E::ts('Contribution: Create'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('CreateContributionWithParameters', '\Civi\ActionProvider\Action\Contribution\CreateContributionWithParameters', E::ts('Contribution: Create (with parameters)'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('UpdateContribution', '\Civi\ActionProvider\Action\Contribution\UpdateContribution', E::ts('Contribution: Update'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('LinkContributionToMembership', '\Civi\ActionProvider\Action\Contribution\LinkContributionToMembership', E::ts('Contribution: Link to membership'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $provider->addAction('LinkContributionToParticipant', '\Civi\ActionProvider\Action\Contribution\LinkContributionToParticipant', E::ts('Contribution: Link to participant'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $provider->addAction('CreateSoftContribution', '\Civi\ActionProvider\Action\Contribution\CreateSoftContribution', E::ts('Contribution: Create soft contribution'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $provider->addAction('MoveContribution', '\Civi\ActionProvider\Action\Contribution\MoveContribution', E::ts('Contribution: Move contribution to another contact'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
  }

}
