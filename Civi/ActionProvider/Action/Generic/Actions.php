<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

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
    $provider->addAction('OptionValueToLabel', '\Civi\ActionProvider\Action\Generic\OptionValueToLabel', E::ts('Other: Show option value(s) as their Label(s)'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('ResolveOptionValues', '\Civi\ActionProvider\Action\Generic\ResolveOptionValues', E::ts('Other: Resolve/map input to option values'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('SetValue', '\Civi\ActionProvider\Action\Generic\SetValue', E::ts('Other: Set Value'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('StringReplaceWithParameter', '\Civi\ActionProvider\Action\Generic\StringReplaceWithParameter', E::ts('Other: Replace text'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('RegexReplaceValue', '\Civi\ActionProvider\Action\Generic\RegexReplaceValue', E::ts('Other: Modify Value with Regular Expression'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('MapValue', '\Civi\ActionProvider\Action\Generic\MapValue', E::ts('Other: Map Value'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('SetParameterValue', '\Civi\ActionProvider\Action\Generic\SetParameterValue', E::ts('Other: Set Value from parameter'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('CalculateValue', '\Civi\ActionProvider\Action\Generic\CalculateValue', E::ts('Other: Calculate value (binary arithmetic operation)'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('FormatValue', '\Civi\ActionProvider\Action\Generic\FormatValue', E::ts('Other: Format Value'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('SetDateValue', '\Civi\ActionProvider\Action\Generic\SetDateValue', E::ts('Other: Set date value'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('ModifyDateValue', '\Civi\ActionProvider\Action\Generic\ModifyDateValue', E::ts('Other: Modify date value'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ParseRawAmount', '\Civi\ActionProvider\Action\Generic\ParseRawAmount', E::ts('Other: Parse raw amount (when you have a weird amount field)'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ParseRawDate', '\Civi\ActionProvider\Action\Generic\ParseRawDate', E::ts('Other: Parse raw date (when you have a weird date field)'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ExplodeList', '\Civi\ActionProvider\Action\Generic\ExplodeList', E::ts('Other: Explode List'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ImplodeList', '\Civi\ActionProvider\Action\Generic\ImplodeList', E::ts('Other: Implode List'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ConcatDateTimeValue', '\Civi\ActionProvider\Action\Generic\ConcatDateTimeValue', E::ts('Other: Concat (merge) a date and a time field to one field'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG
    ));
    $provider->addAction('DownloadFileLink', '\Civi\ActionProvider\Action\Generic\DownloadFileLink', E::ts('Other: Link to download file'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('GetFileInfo', '\Civi\ActionProvider\Action\Generic\GetFileInfo', E::ts('Other: Get file info'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('ExportFile', '\Civi\ActionProvider\Action\Generic\ExportFile', E::ts('Other: Export file'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('ReuploadFile', '\Civi\ActionProvider\Action\Generic\ReuploadFile', E::ts('Other: Reuse existing file'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $provider->addAction('ReplaceTokensInHTML', '\Civi\ActionProvider\Action\Generic\ReplaceTokensInHTML', E::ts('Other: Replace tokens in HTML'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG
    ));
    $provider->addAction('ReplaceEntityTokensInHTML', '\Civi\ActionProvider\Action\Generic\ReplaceEntityTokensInHTML', E::ts('Other: Replace entity tokens in HTML'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG
    ));
    $provider->addAction('Geocode', '\Civi\ActionProvider\Action\Generic\Geocode', E::ts('Other: Retrieve Geocoding (Latitude, Longitude)'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $provider->addAction('LoggedInContact', '\Civi\ActionProvider\Action\Generic\LoggedInContact', E::ts('Other: Logged-in Contact Id'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $provider->addAction('IncrementIntegerValue', '\Civi\ActionProvider\Action\Generic\IncrementIntegerValue', E::ts('Other: Increment integer value'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG
    ));
    $provider->addAction('SumInputFields', '\Civi\ActionProvider\Action\Generic\SumInputFields', E::ts('Other: Sum up to 6 input fields'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG
    $provider->addAction('Concatenate', '\Civi\ActionProvider\Action\Generic\ConcatenateValues', E::ts('Other: Concatenate values'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('SwapParticipantById', '\Civi\ActionProvider\Action\Event\SwapParticipantById', E::ts('Event: change/swap the contact_id on a participant') , array(
      AbstractAction::DATA_MANIPULATION_TAG
    ));
    $provider->addAction('AddNode', '\Civi\ActionProvider\Action\Generic\AddNote', E::ts('Other: Add Note'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG, AbstractAction::DATA_MANIPULATION_TAG,
    ));
  }

}
