<?php

namespace Civi\ActionProvider\Utils;

use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;


use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\SpecificationGroup;

/**
 * Helper class to add a configuration specification from custom field
 */
class CustomField {

  /**
   * Gets the data type of the custom field.
   */
  public static function getTypeForCustomField($field) {
    $type = $field['data_type'];
    return Type::convertCrmType($type);
  }

  /**
   * Returns the name of the custom group
   *
   * @param int $custom_group_id
   * @return string
   */
  public static function getCustomGroupName($custom_group_id) {
    return ConfigContainer::getInstance()->getCustomGroupName($custom_group_id);
  }

  /**
   * Returns a formatted name as custom_CustomGroupName_CustomFieldName
   *
   * @param int $custom_field_id
   * @return string
   */
  public static function getCustomFieldName($custom_field_id) {
    $customField = ConfigContainer::getInstance()->getCustomField($custom_field_id);
    $custom_group_name = self::getCustomGroupName($customField['custom_group_id']);
    $name = 'custom_'.$custom_group_name.'_'.$customField['name'];
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
    $name = self::getCustomFieldName($customField['id']);
    $apiFieldName = 'custom_'.$customField['id'];
    $type = self::getTypeForCustomField($customField);
    $title = trim($titlePrefix.$customField['label']);
    $is_required = isset($customField['is_required']) && $customField['is_required'] && $useRequiredFromCustomField ? true : false;
    $multiple = false;
    if ($customField['html_type'] == 'CheckBox') {
      $multiple = true;
    }
    if ($customField['html_type'] == 'Multi-Select') {
      $multiple = true;
    }
    // File custom fields shouldn't be selectable - use the "upload file to a custom field" action instead.
    if ($customField['html_type'] == 'File') {
      return null;
    }
    $default = null;
    $spec = null;

    if (isset($customField['option_group_id']) && $customField['option_group_id']) {
      $spec = new OptionGroupSpecification($name, $customField['option_group_id'], $title, $is_required, $default, $multiple);
    }
    elseif($type) {
      $fkEntity = $customField['fk_entity'] ?? null;
      $spec = new Specification($name, $type, $title, $is_required, $default, $fkEntity, array(), $multiple);
    }
    if ($spec) {
      $spec->setApiFieldName($apiFieldName);
      return $spec;
    }
    return null;
  }

  /**
   * Returns a specification for custom groups and fields
   *
   * @param $customGroupId
   * @param $customGroupName
   * @param $customGroupTitle
   *
   * @return \Civi\ActionProvider\Parameter\SpecificationGroup
   * @throws \CiviCRM_API3_Exception
   */
  public static function getSpecForCustomGroup($customGroupId, $customGroupName, $customGroupTitle) {
    $customFields = ConfigContainer::getInstance()->getCustomFieldsOfCustomGroup($customGroupId);
    $customGroupSpecBag = new SpecificationBag();
    foreach ($customFields as $customField) {
      if ($customField['is_active']) {
        $spec = self::getSpecFromCustomField($customField, '', FALSE);
        if ($spec) {
          $customGroupSpecBag->addSpecification($spec);
        }
      }
    }
    return new SpecificationGroup($customGroupName, $customGroupTitle, $customGroupSpecBag);
  }

  /**
   * Returns an array with the api parameters for the custom fields.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   * @param \Civi\ActionProvider\Parameter\SpecificationBag $parameterSpecification
   *
   * @return array
   */
  public static function getCustomFieldsApiParameter(ParameterBagInterface $parameters, SpecificationBag $parameterSpecification) {
    $apiParams = array();
    foreach($parameterSpecification as $spec) {
      if ($spec instanceof SpecificationGroup) {
        foreach($spec->getSpecificationBag() as $subSpec) {
          if (stripos($subSpec->getName(), 'custom_')===0 && $parameters->doesParameterExists($subSpec->getName())) {
            $apiParams[$subSpec->getApiFieldName()] = $parameters->getParameter($subSpec->getName());
          }
        }
      } elseif (stripos($spec->getName(), 'custom_')===0) {
        if ($parameters->doesParameterExists($spec->getName())) {
          $apiParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
        }
      }
    }
    return $apiParams;
  }

}
