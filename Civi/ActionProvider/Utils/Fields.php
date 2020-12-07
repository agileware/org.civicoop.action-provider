<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

class Fields {

  public static function getFieldsForEntity(SpecificationBag $specs, $entity, $api_action='get', $fieldsToSkip=array()) {
    $fields = civicrm_api3($entity, 'getfields', array('api_action' => $api_action));
    foreach($fields['values'] as $field) {
      if (in_array($field['name'], $fieldsToSkip)) {
        continue;
      }
      if (stripos($field['name'], 'custom_') !== 0) {
        $options = null;
        try {
          $option_api = civicrm_api3($entity, 'getoptions', ['field' => $field['name']]);
          if (isset($option_api['values']) && is_array($option_api['values'])) {
            $options = $option_api['values'];
          }
        } catch (\Exception $e) {
          // Do nothing
        }

        $type = \CRM_Utils_Type::typeToString($field['type']);
        if ($type) {
          $type = Type::convertCrmType($type);
          $spec = new Specification($field['name'], $type, $field['title'], FALSE, NULL, NULL, $options, FALSE);
          $specs->addSpecification($spec);
        }
      }
    }

    $customGroups = ConfigContainer::getInstance()->getCustomGroupsForEntity($entity);
    foreach ($customGroups as $customGroup) {
      if ($customGroup['is_active']) {
        $customFields = ConfigContainer::getInstance()->getCustomFieldsOfCustomGroup($customGroup['id']);
        foreach ($customFields as $customField) {
          if ($customField['is_active']) {
            $spec = CustomField::getSpecFromCustomField($customField, $customGroup['title'] . ': ', FALSE);
            if ($spec) {
              $specs->addSpecification($spec);
            }
          }
        }
      }
    }
  }

}
