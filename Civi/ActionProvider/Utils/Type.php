<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

class Type {

  public static function convertCrmType($type) {
    switch ($type) {
      case 'Int':
      case 'File':
      case 'ContactReference':
      case 'EntityReference':
        return 'Integer';
        break;
      case 'Memo':
        return 'Text';
        break;
      case 'StateProvince':
      case 'Country':
      case 'Link':
        return 'String';
        break;
    }
    return $type;
  }

}
