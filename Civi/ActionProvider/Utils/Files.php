<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

class Files {

  /**
   * Creates a directory under templates_c/action_provider (temporary)
   * or the custom file upload dir (permanent).
   *
   * And adds a .htaccess to this directory so files could not be downloaded directly from the server.
   *
   * @param string $dir
   *   The subdirectory without / on the end. E.g. createpdf or createpdf/batch1233
   * @param bool $temporaryStorage
   *   choose between temporary or permanent storage
   * @return string
   */
  public static function createRestrictedDirectory($dir, bool $temporaryStorage = TRUE) {
    $subDir = 'action_provider/' . $dir;
    $fullPath = self::getStorageBaseDir($temporaryStorage) . $subDir;
    if (is_dir($fullPath)) {
      \CRM_Utils_File::restrictAccess($fullPath . '/');
      return $subDir;
    }

    \CRM_Utils_File::createDir($fullPath);
    \CRM_Utils_File::restrictAccess($fullPath . '/');
    return $subDir;
  }

  /**
   * Returns the base directory in which restricted action provider directories
   * are created.
   *
   * @param bool $temporaryStorage
   * @return string
   */
  public static function getStorageBaseDir(bool $temporaryStorage = TRUE) {
    $config = \CRM_Core_Config::singleton();
    return $temporaryStorage ? $config->templateCompileDir : $config->customFileUploadDir;
  }

}
