<?php
use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_ActionProvider_Upgrader extends \CRM_Extension_Upgrader_Base {

  /**
   * Add the Download Entity
   *
   * @return TRUE on success
   * @throws CRM_Core_Exception
   */
  public function upgrade_1001(): bool {
     E::schema()->createEntityTable('upgrade/1001/ActionProviderDownload.entityType.php');
     return TRUE;
  }
}
