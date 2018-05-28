<?php

namespace Civi\ActionProvider\Utils;

use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;


use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Helper class to add a configuration specification from custom field
 */
class CustomField {
  
  /**
   * Gets the data type of the custom field.
   */
  public static function getTypeForCustomField($field) {
    $type = $field['data_type'];
    switch ($type) {
      case 'Int':
      case 'ContactReference':
        $type = 'Integer';
        break;
      case 'File':
        $type = null;
        break;  
    }
    return $type;
  }
  
  /**
   * Converts a specifcation object to a custom field.
   * 
   * @param array
   *   The custom field data
   * @param string
   * @param bool
   *   When this param is true then the required state is taken over from the custom field. 
   *   Other wise the field is not required.
   * @return Specification|null
   */
  public static function getSpecFromCustomField($customField, $titlePrefix='', $useRequiredFromCustomField=false) {
    $name = 'custom_'.$customField['id'];
    $type = self::getTypeForCustomField($customField);
    $title = trim($titlePrefix.$customField['label']);
    $is_required = $customField['is_required'] && $useRequiredFromCustomField ? true : false;
    $multiple = false;
    $default = null;
    
    if (isset($customField['option_group_id']) && $customField['option_group_id']) {
      return new OptionGroupSpecification($name, $customField['option_group_id'], $title, $is_required, $default, $multiple);
    } elseif($type) {
      return new Specification($name, $type, $title, $is_required, $default, null, array(), $multiple);
    }
    
    return null;
  }
  
}
