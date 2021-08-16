<?php

require_once 'action_provider.civix.php';
use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function action_provider_civicrm_container($container) {
  if (!$container->has('action_provider')) {
    // Only add our container when it does not exists.
    // This way other extensions might override the container method.
    $actionProviderDefinition = \Civi\ActionProvider\Symfony\Component\DependencyInjection\DefinitionAdapter::createDefinitionClass('Civi\ActionProvider\Container');
    $actionProviderDefinition->setFactory(['Civi\ActionProvider\Container', 'getinstance']);
    $container->setDefinition('action_provider', $actionProviderDefinition);
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post/
 *
 * @param $op
 * @param $objectName
 * @param $objectId
 * @param $objectRef
 */
function action_provider_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  Civi\ActionProvider\ConfigContainer::postHook($op, $objectName, $objectId, $objectRef);
}

/**
 * Implements hook_civicrm_config().
 */
function action_provider_civicrm_config(&$config) {
  _action_provider_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 */
function action_provider_civicrm_xmlMenu(&$files) {
  _action_provider_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 */
function action_provider_civicrm_install() {
  _action_provider_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 */
function action_provider_civicrm_postInstall() {
  _action_provider_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 */
function action_provider_civicrm_uninstall() {
  _action_provider_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 */
function action_provider_civicrm_enable() {
  _action_provider_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 */
function action_provider_civicrm_disable() {
  _action_provider_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 */
function action_provider_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _action_provider_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 */
function action_provider_civicrm_managed(&$entities) {
  _action_provider_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 */
function action_provider_civicrm_caseTypes(&$caseTypes) {
  _action_provider_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 */
function action_provider_civicrm_angularModules(&$angularModules) {
  _action_provider_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 */
function action_provider_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _action_provider_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
