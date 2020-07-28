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
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('SetValue', '\Civi\ActionProvider\Action\Generic\SetValue', E::ts('Other: Set Value'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('RegexReplaceValue', '\Civi\ActionProvider\Action\Generic\RegexReplaceValue', E::ts('Other: Modify Value with Regular Expression'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('MapValue', '\Civi\ActionProvider\Action\Generic\MapValue', E::ts('Other: Map Value'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('SetParameterValue', '\Civi\ActionProvider\Action\Generic\SetParameterValue', E::ts('Other: Set Value from parameter'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('CalculateValue', '\Civi\ActionProvider\Action\Generic\CalculateValue', E::ts('Other: Calculate value (binary arithmetic operation)'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('FormatValue', '\Civi\ActionProvider\Action\Generic\FormatValue', E::ts('Other: Format Value'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('SetDateValue', '\Civi\ActionProvider\Action\Generic\SetDateValue', E::ts('Other: Set date value'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $provider->addAction('ModifyDateValue', '\Civi\ActionProvider\Action\Generic\ModifyDateValue', E::ts('Other: Modify date value'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ExplodeList', '\Civi\ActionProvider\Action\Generic\ExplodeList', E::ts('Other: Explode List'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $provider->addAction('ConcatDateTimeValue', '\Civi\ActionProvider\Action\Generic\ConcatDateTimeValue', E::ts('Other: Concat (merge) a date and a time field to one field'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
  }

}
