<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

class FileWriter {

  /**
   * Write to a file in a restricted directory. E.g. a directory which is not accesible by
   * a url.
   *
   * @param $contents
   * @param $filename
   * @return string
   *   The full file path.
   */
  public static function writeFile($contents, $filename) {
    $basePath = \CRM_Core_Config::singleton()->templateCompileDir . 'action_provider';
    \CRM_Utils_File::createDir($basePath);
    \CRM_Utils_File::restrictAccess($basePath.'/');
    $fullFilePath = $basePath.'/'. $filename;

    file_put_contents($fullFilePath, $contents);

    return $fullFilePath;
  }

}