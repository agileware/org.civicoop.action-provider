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

    if (!$path || !file_exists($path)) {
      CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $now = gmdate('D, d M Y H:i:s') . ' GMT';
    CRM_Utils_System::setHttpHeader('Content-Type', $mimeType);
    CRM_Utils_System::setHttpHeader('Expires', $now);
    // lem9 & loic1: IE needs specific headers
    $isIE = empty($_SERVER['HTTP_USER_AGENT']) ? FALSE : strstr($_SERVER['HTTP_USER_AGENT'], 'MSIE');
    $fileString = "filename=\"{$downloadName}\"";
    if ($isIE) {
      CRM_Utils_System::setHttpHeader("Content-Disposition", "inline; $fileString");
      CRM_Utils_System::setHttpHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
      CRM_Utils_System::setHttpHeader('Pragma', 'public');
    }
    else {
      CRM_Utils_System::setHttpHeader("Content-Disposition", "download; $fileString");
      CRM_Utils_System::setHttpHeader('Pragma', 'no-cache');
    }

    print readfile($path);
    CRM_Utils_System::civiExit();
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
