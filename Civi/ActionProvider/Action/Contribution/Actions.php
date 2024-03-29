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
    $provider->addAction('GetContribution', '\Civi\ActionProvider\Action\Contribution\GetContribution', E::ts('Contribution: Get data'), [
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('GetContributionByCustomField', '\Civi\ActionProvider\Action\Contribution\GetContributionByCustomField', E::ts('Contribution: Get data by custom field'), [
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('CreateContribution', '\Civi\ActionProvider\Action\Contribution\CreateContribution', E::ts('Contribution: Create'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('CreateContributionWithParameters', '\Civi\ActionProvider\Action\Contribution\CreateContributionWithParameters', E::ts('Contribution: Create (with parameters)'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('CreateContributionRecur', '\Civi\ActionProvider\Action\Contribution\CreateContributionRecur', E::ts('Contribution: Create recurring contribution'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('UpdateContributionRecur', '\Civi\ActionProvider\Action\Contribution\UpdateContributionRecur', E::ts('Contribution: Update recurring contribution'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('ContributionRecurRepeatTransaction', '\Civi\ActionProvider\Action\Contribution\ContributionRecurRepeatTransaction', E::ts('Contribution: repeat recurring contribution'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('UpdateContribution', '\Civi\ActionProvider\Action\Contribution\UpdateContribution', E::ts('Contribution: Update'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('CreatePayment', '\Civi\ActionProvider\Action\Contribution\CreatePayment', E::ts('Contribution: Create Payment'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('CreateLineItem', '\Civi\ActionProvider\Action\Contribution\CreateLineItem', E::ts('Contribution: Create Line Item'), [
        AbstractAction::DATA_MANIPULATION_TAG,
        AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('LinkContributionToMembership', '\Civi\ActionProvider\Action\Contribution\LinkContributionToMembership', E::ts('Contribution: Link to membership'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('LinkContributionToParticipant', '\Civi\ActionProvider\Action\Contribution\LinkContributionToParticipant', E::ts('Contribution: Link to participant'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('CreateSoftContribution', '\Civi\ActionProvider\Action\Contribution\CreateSoftContribution', E::ts('Contribution: Create soft contribution'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('MoveContribution', '\Civi\ActionProvider\Action\Contribution\MoveContribution', E::ts('Contribution: Move contribution to another contact'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('Invoice', '\Civi\ActionProvider\Action\Contribution\Invoice', E::ts('Contribution: Invoice'), [
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('SendConfirmation', '\Civi\ActionProvider\Action\Contribution\SendConfirmation', E::ts('Contribution: Send confirmation / receipt'), [
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
  }

}
