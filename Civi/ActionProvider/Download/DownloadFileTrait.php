<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Download;

use Civi\Api4\ActionProviderDownload;
use CRM_ActionProvider_ExtensionUtil as E;

trait DownloadFileTrait {

  protected function createDownloadStatusMessage(string $title, string $fileName, string $subDir, string $downloadName) {
    ActionProviderDownload::create(FALSE)->setValues([
      'file_name' => $fileName,
      'sub_dir' => $subDir,
      'download_name' => $downloadName,
    ])
    ->execute();

    $downloadUrl = \CRM_Utils_System::url('civicrm/actionprovider/downloadfile', [
      'filename' => $fileName,
      'subdir' => $subDir,
      'downloadname' => $downloadName
    ]);
    \CRM_Core_Session::setStatus(E::ts('<a href="%1" class="action-provider start-download">Download document(s)<a/>', [1 => $downloadUrl]), $title, 'success');
  }

}