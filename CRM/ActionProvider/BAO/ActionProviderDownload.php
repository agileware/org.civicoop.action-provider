<?php

use Civi\Api4\ActionProviderDownload;
use Civi\Core\HookInterface;
use CRM_ActionProvider_ExtensionUtil as E;

class CRM_ActionProvider_BAO_ActionProviderDownload extends CRM_ActionProvider_DAO_ActionProviderDownload implements HookInterface {

  /**
   * Event fired before an action is taken on a User record.
   * @param \Civi\Core\Event\PreEvent $event
   */
  public static function self_hook_civicrm_pre(\Civi\Core\Event\PreEvent $event) {
    if (in_array($event->action, ['create'], TRUE)) {
      if (empty($event->params['created_date'])) {
        $event->params['created_date'] = date('YmdHis');
      }
      if (empty($event->params['expire_date'])) {
        $event->params['expire_date'] = date('YmdHis', strtotime('+1 year'));
      }
      if (empty($event->params['contact_id'])) {
        $event->params['contact_id'] = CRM_Core_Session::getLoggedInContactID();
      }
    }
    if (in_array($event->action, ['delete'], TRUE) && $event->id) {
      $download = ActionProviderDownload::get(FALSE)->addWhere('id', '=', $event->id)->execute()->first();
      $subdir = $download['sub_dir'];
      $fileName = $download['file_name'];
      $basePath = CRM_Core_Config::singleton()->customFileUploadDir . $subdir;
      $path = $basePath.DIRECTORY_SEPARATOR.$fileName;
      if (file_exists($path)) {
        unlink($path);
      }
    }
  }


}
