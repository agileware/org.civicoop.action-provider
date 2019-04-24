<?php
use CRM_ActionProvider_ExtensionUtil as E;

class CRM_ActionProvider_Page_DownloadFile extends CRM_Core_Page {

  public function run() {
    $fileName = CRM_Utils_Request::retrieve('filename', 'String', $this, FALSE);
    $downloadName = CRM_Utils_Request::retrieve('downloadname', 'String', $this, FALSE);
    $subdir = CRM_Utils_Request::retrieve('subdir', 'String', $this, FALSE);
    if (empty($fileName)) {
      CRM_Core_Error::statusBounce("Cannot access file");
    }

    if (!self::isValidFileName($fileName)) {
      CRM_Core_Error::statusBounce("Malformed filename");
    }

    $basePath = CRM_Core_Config::singleton()->templateCompileDir . $subdir;
    $path = $basePath.'/'.$fileName;
    $mimeType = mime_content_type($path);

    if (!$path) {
      CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $buffer = file_get_contents($path);
    if (!$buffer) {
      CRM_Core_Error::statusBounce('The file is either empty or you do not have permission to retrieve the file');
    }

    CRM_Utils_System::download(
      $downloadName,
      $mimeType,
      $buffer,
      NULL,
      TRUE,
      'download'
    );
  }

  /**
   * Is the filename a safe and valid filename passed in from URL
   *
   * @param string $fileName
   * @return bool
   */
  protected static function isValidFileName($fileName = NULL) {
    if ($fileName) {
      $check = $fileName !== basename($fileName) ? FALSE : TRUE;
      if ($check) {
        if (substr($fileName, 0, 1) == '/' || substr($fileName, 0, 1) == '.' || substr($fileName, 0, 1) == DIRECTORY_SEPARATOR) {
          $check = FALSE;
        }
      }
      return $check;
    }
    return FALSE;
  }

}
