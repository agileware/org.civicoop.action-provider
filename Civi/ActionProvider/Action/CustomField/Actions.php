<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CustomField;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Provider;
use CRM_ActionProvider_ExtensionUtil as E;

class Actions {

  /**
   * Load custom field actions
   *
   * @param \Civi\ActionProvider\Provider $provider
   */
  public static function loadActions(Provider $provider) {
    $provider->addAction('CustomFieldAddMultiValue', '\Civi\ActionProvider\Action\CustomField\AddMultiValue', E::ts('Custom Field: Add value(s) to a multi value custom field'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
  }

}
