<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider;

use Symfony\Component\DependencyInjection\ContainerBuilder;

class Config extends \Symfony\Component\DependencyInjection\Container {

  /**
   * Returns the name of the custom group
   *
   * @param int $custom_group_id
   * @return string
   */
  public function getCustomGroupName($custom_group_id) {
    $customGroupNames = $this->getParameter('custom_group_names');
    return $customGroupNames[$custom_group_id];
  }

  /**
   * Returns the custom field api data.
   * @param $custom_field_id
   * @return array
   */
  public function getCustomField($custom_field_id) {
    $customFields = $this->getParameter('custom_fields');
    return $customFields[$custom_field_id];
  }

  /**
   * Returns with custom fields of a certain group.
   *
   * @param $custom_group_id
   * @return array
   */
  public function getCustomFieldsOfCustomGroup($custom_group_id) {
    $customFieldsPerGroup = $this->getParameter('custom_fields_per_group');
    return $customFieldsPerGroup[$custom_group_id];
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
      $customFields[$customField['id']] = $customField;
      $customFieldsPerGroup[$customField['custom_group_id']][] = $customField;
    }

    $containerBuilder->setParameter('custom_group_names', $customGroupNames);
    $containerBuilder->setParameter('custom_groups_per_extends', $customGroupPerExtends);
    $containerBuilder->setParameter('custom_fields_per_group', $customFieldsPerGroup);
    $containerBuilder->setParameter('custom_fields', $customFields);

  }

}
