<?php

namespace Civi\ActionProvider\Utils;

use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;


use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\SpecificationGroup;
use CRM_ActionProvider_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
    return Type::convertCrmType($type);
  }

  /**
   * Returns the name of the custom group
   *
   * @param int $custom_group_id
   * @return string
   */
  public static function getCustomGroupName($custom_group_id) {
    $config = ConfigContainer::getInstance();
    $customGroupNames = $config->getParameter('custom_group_names');
    return $customGroupNames[$custom_group_id];
  }

  /**
   * Returns a formatted name as custom_CustomGroupName_CustomFieldName
   *
   * @param int $custom_field_id
   * @return string
   */
  public static function getCustomFieldName($custom_field_id) {
    $config = ConfigContainer::getInstance();
    $customFields = $config->getParameter('custom_fields');

    $custom_group_name = self::getCustomGroupName($customFields[$custom_field_id]['custom_group_id']);
    $name = 'custom_'.$custom_group_name.'_'.$customFields[$custom_field_id]['name'];
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
    $is_required = isset($customField['is_required']) && $customField['is_required'] && $useRequiredFromCustomField ? true : false;
    $multiple = false;
    if ($customField['html_type'] == 'CheckBox') {
      $multiple = true;
    }
    if ($customField['html_type'] == 'Multi-Select') {
      $multiple = true;
    }
    $default = null;
    $spec = null;

    if (isset($customField['option_group_id']) && $customField['option_group_id']) {
      $spec = new OptionGroupSpecification($name, $customField['option_group_id'], $title, $is_required, $default, $multiple);
    } elseif($type) {
      $spec = new Specification($name, $type, $title, $is_required, $default, null, array(), $multiple);
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
    $config = ConfigContainer::getInstance();
    $customFieldsPerGroup = $config->getParameter('custom_fields_per_group');
    $customGroupSpecBag = new SpecificationBag();
    foreach ($customFieldsPerGroup[$customGroupId] as $customField) {
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

  public static function buildConfigContainer(ContainerBuilder $containerBuilder) {
    $customGroupNames = array();
    $customGroupPerExtends = array();
    $customFields = array();
    $customFieldsPerGroup = array();
    $customGroupApi = civicrm_api3('CustomGroup', 'get', ['options' => ['limit' => 0]]);
    foreach($customGroupApi['values'] as $customGroup) {
      $customGroupNames[$customGroup['id']] = $customGroup['name'];
      $customGroupPerExtends[$customGroup['extends']][] = $customGroup;
    }
    $customFieldsApi = civicrm_api3('CustomField', 'get', ['options' => ['limit' => 0]]);
    foreach($customFieldsApi['values'] as $customField) {
      $customFields[] = $customField;
      $customFieldsPerGroup[$customField['custom_group_id']][] = $customField;
    }

    $containerBuilder->setParameter('custom_group_names', $customGroupNames);
    $containerBuilder->setParameter('custom_groups_per_extends', $customGroupPerExtends);
    $containerBuilder->setParameter('custom_fields_per_group', $customFieldsPerGroup);
    $containerBuilder->setParameter('custom_fields', $customFields);

  }

}
