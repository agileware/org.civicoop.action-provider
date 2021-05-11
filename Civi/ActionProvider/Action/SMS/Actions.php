<?php

namespace Civi\ActionProvider\Action\SMS;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Provider;
use CRM_ActionProvider_ExtensionUtil as E;

class Actions {

  /**
   * Load sms actions
   *
   * @param \Civi\ActionProvider\Provider $provider
   */

  public static function loadActions(Provider $provider) {
    $provider->addAction('SMSSend', '\Civi\ActionProvider\Action\SMS\SMSSend', E::ts('SMS: Create and send mass sms'), array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
      'sms',
    ));
  }
}
