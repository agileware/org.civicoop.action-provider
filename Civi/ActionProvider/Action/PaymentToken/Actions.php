<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\PaymentToken;

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
    $provider->addAction('CreateOrUpdatePaymentToken', '\Civi\ActionProvider\Action\PaymentToken\CreateOrUpdatePaymentToken', E::ts('Payment Token: Create or Update'), [
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
    $provider->addAction('GetPaymentToken', '\Civi\ActionProvider\Action\PaymentToken\GetPaymentToken', E::ts('Payment Token: Get Single'), [
      AbstractAction::DATA_RETRIEVAL_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ]);
		}

}
