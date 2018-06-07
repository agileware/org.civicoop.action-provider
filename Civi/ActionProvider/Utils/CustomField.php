<?php

namespace Civi\ActionProvider\Utils;

use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;


use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Helper class to add a configuration specification from custom field
 */
class CustomField {
  
  static $customGroupNames = array();
  
  static $customFields = array();
  
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
   * Returns the name of the custom group
   * 
   * @param int $custom_group_id
   * @return string
   */
  public static function getCustomGroupName($custom_group_id) {
    if (!isset(self::$customGroupNames[$custom_group_id])) {
      self::$customGroupNames[$custom_group_id] = civicrm_api3('CustomGroup', 'getvalue', array('return' => 'name', 'id' => $custom_group_id));
    }
    return self::$customGroupNames[$custom_group_id];
  }
  
  /**
   * Returns a formatted name as custom_CustomGroupName_CustomFieldName
   * 
   * @param int $custom_field_id
   * @return string
   */
  public static function getCustomFieldName($custom_field_id) {
    if (!isset(self::$customFields[$custom_field_id])) {
      self::$customFields[$custom_field_id] = civicrm_api3('CustomField', 'getsingle', array('id' => $custom_field_id));
    }
    
    $custom_group_name = self::getCustomGroupName(self::$customFields[$custom_field_id]['custom_group_id']);
    $name = 'custom_'.$custom_group_name.'_'.self::$customFields[$custom_field_id]['name'];  
    return $name;
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
    self::$customFields[$customField['id']] = $customField;
    
    $name = self::getCustomFieldName($customField['id']); 
    $apiFieldName = 'custom_'.$customField['id'];
    $type = self::getTypeForCustomField($customField);
    $title = trim($titlePrefix.$customField['label']);
    $is_required = $customField['is_required'] && $useRequiredFromCustomField ? true : false;
    $multiple = false;
    $default = null;
    $spec = null;
    
    if (isset($customField['option_group_id']) && $customField['option_group_id']) {
      $spec = new OptionGroupSpecification($name, $customField['option_group_id'], $title, $is_required, $default, $multiple);
    } elseif($type) {
      $spec = new Specification($name, $type, $title, $is_required, $default, null, array(), $multiple);
    }
    if ($spec) {
      $spec->setApiFieldName($apiFieldName);
    }
    
    return $spec;
  }
  
}
