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
  public function getCustomGroup($custom_group_id) {
    $customGroups = $this->getParameter('custom_groups');
    return $customGroups[$custom_group_id];
  }

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

  /**
   * Returns an array of custom groups for an entity.
   *
   * @param $entity
   * @return array
   */
  public function getCustomGroupsForEntity($entity) {
    $customGroupExtends = $this->getParameter('custom_groups_per_extends');
    $customGroups = isset($customGroupExtends[$entity]) ? $customGroupExtends[$entity] : [];
    switch($entity) {
      case 'Individual':
      case 'Household':
      case 'Organization':
        $customGroups = array_merge($customGroups, isset($customGroupExtends['Contact']) ? $customGroupExtends['Contact'] : []);
        break;
    }
    return $customGroups;
  }

  /**
   * Returns an array of custom groups for an entity.
   *
   * @param array $entities
   * @return array
   */
  public function getCustomGroupsForEntities($entities) {
    $customGroupExtends = $this->getParameter('custom_groups_per_extends');
    $customGroups = [];
    foreach($entities as $entity) {
      $customGroups = array_merge($customGroups, isset($customGroupExtends[$entity]) ? $customGroupExtends[$entity] : []);
    }
    return $customGroups;
  }

  /**
   * Build the container with the custom field and custom groups.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
   * @throws \CiviCRM_API3_Exception
   */
  public static function buildConfigContainer(ContainerBuilder $containerBuilder) {
    $customGroups = array();
    $customGroupNames = array();
    $customGroupPerExtends = array();
    $customFields = array();
    $customFieldsPerGroup = array();
    $customGroupApi = civicrm_api3('CustomGroup', 'get', ['options' => ['limit' => 0]]);
    foreach($customGroupApi['values'] as $customGroup) {
      $customGroups[$customGroup['id']] = $customGroup;
      $customGroupNames[$customGroup['id']] = $customGroup['name'];
      $customGroupPerExtends[$customGroup['extends']][] = $customGroup;
    }
    $customFieldsApi = civicrm_api3('CustomField', 'get', ['options' => ['limit' => 0]]);
    foreach($customFieldsApi['values'] as $customField) {
      $customFields[$customField['id']] = $customField;
      $customFieldsPerGroup[$customField['custom_group_id']][] = $customField;
    }

    $customGroupNames = $containerBuilder->getParameterBag()->escapeValue($customGroupNames);
    $customGroupPerExtends = $containerBuilder->getParameterBag()->escapeValue($customGroupPerExtends);
    $customFieldsPerGroup = $containerBuilder->getParameterBag()->escapeValue($customFieldsPerGroup);
    $customGroups = $containerBuilder->getParameterBag()->escapeValue($customGroups);
    $customFields = $containerBuilder->getParameterBag()->escapeValue($customFields);

    $containerBuilder->setParameter('custom_groups', $customGroups);
    $containerBuilder->setParameter('custom_group_names', $customGroupNames);
    $containerBuilder->setParameter('custom_groups_per_extends', $customGroupPerExtends);
    $containerBuilder->setParameter('custom_fields_per_group', $customFieldsPerGroup);
    $containerBuilder->setParameter('custom_fields', $customFields);

  }

}
