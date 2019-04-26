<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

class Files {

  /**
   * Creates a directory under templates_c/action_provider
   * And adds a .htaccess to this directory so files could not be downloaded directly from the server.
   *
   * @param string $dir
   *   The subdirectory without / on the end. E.g. createpdf or createpdf/batch1233
   * @return string
   */
  public static function createRestrictedDirectory($dir) {
    $subDir = 'action_provider/'.$dir;
    $fullPath = \CRM_Core_Config::singleton()->templateCompileDir . $subDir;
    if (is_dir($fullPath)) {
      \CRM_Utils_File::restrictAccess($fullPath.'/');
      return $subDir;
    }

    \CRM_Utils_File::createDir($fullPath);
    \CRM_Utils_File::restrictAccess($fullPath.'/');
    return $subDir;
  }

}