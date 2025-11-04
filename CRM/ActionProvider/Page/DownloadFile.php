<?php
use CRM_ActionProvider_ExtensionUtil as E;

class CRM_ActionProvider_Page_DownloadFile extends CRM_Core_Page {

  // 4 MB
  public const CHUNK_SIZE_BYTES = 8 * (1024 * 1024);

  // 1 hour
  public const TIMEOUT_SECONDS = 3600;

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

    $basePath = CRM_Core_Config::singleton()->customFileUploadDir . $subdir;
    $path = $basePath.DIRECTORY_SEPARATOR.$fileName;

    if (!$path || !file_exists($path)) {
      $basePath = CRM_Core_Config::singleton()->templateCompileDir . $subdir;
      $path = $basePath.DIRECTORY_SEPARATOR.$fileName;
    }

    if (!$path || !file_exists($path)) {
      CRM_Core_Error::statusBounce('Could not retrieve the file');
    }

    $mimeType = mime_content_type($path);
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

    $fileSize = (int) sprintf("%u", filesize($path));
    CRM_Utils_System::setHttpHeader('Content-Length', $fileSize);
    CRM_Utils_System::setHttpHeader('Content-Type', 'application/octet-stream');
    CRM_Utils_System::setHttpHeader('Content-Transfer-Encoding', 'binary');

    // prevent timeouts
    set_time_limit(self::TIMEOUT_SECONDS);

    if ($fileSize > self::CHUNK_SIZE_BYTES) {
      // use chunks...
      $fileHandle = fopen($path, 'rb');
      while (!feof($fileHandle)) {
        print(@fread($fileHandle, self::CHUNK_SIZE_BYTES));
        ob_flush();
        flush();
      }
      fclose($fileHandle);
    }
    else {
      // output in one run
      readfile($path);
    }

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
