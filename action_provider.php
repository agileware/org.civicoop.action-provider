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
 * Implements hook_civicrm_alterBundle().
 */
function action_provider_civicrm_alterBundle(CRM_Core_Resources_Bundle $bundle) {
  if ($bundle->name !== 'coreResources') {
    return;
  }
  $bundle->addScriptFile('action-provider', 'js/action-provider.js');
}

/**
 * Implements hook_civicrm_config().
 */
function action_provider_civicrm_config(&$config) {
  _action_provider_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 */
function action_provider_civicrm_install() {
  _action_provider_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 */
function action_provider_civicrm_enable() {
  _action_provider_civix_civicrm_enable();
}
