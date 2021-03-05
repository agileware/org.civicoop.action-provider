<?php

require_once 'action_provider.civix.php';
use CRM_ActionProvider_ExtensionUtil as E;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Implements hook_civicrm_container()
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_container/
 */
function action_provider_civicrm_container($container) {
	if (!$container->has('action_provider')) {
    // Only add our container when it does not exists.
    // This way other extensions might override the container method.
    $actionProviderDefinition = new Definition('Civi\ActionProvider\Container');
    $actionProviderDefinition->setFactory(['Civi\ActionProvider\Container', 'getinstance']);
    $actionProviderDefinition->setPrivate(FALSE);
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
  \Civi\ActionProvider\ConfigContainer::postHook($op, $objectName, $objectId, $objectRef);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function action_provider_civicrm_config(&$config) {
  _action_provider_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function action_provider_civicrm_xmlMenu(&$files) {
  _action_provider_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function action_provider_civicrm_install() {
  _action_provider_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function action_provider_civicrm_postInstall() {
  _action_provider_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function action_provider_civicrm_uninstall() {
  _action_provider_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function action_provider_civicrm_enable() {
  _action_provider_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function action_provider_civicrm_disable() {
  _action_provider_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function action_provider_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _action_provider_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function action_provider_civicrm_managed(&$entities) {
  _action_provider_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function action_provider_civicrm_caseTypes(&$caseTypes) {
  _action_provider_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function action_provider_civicrm_angularModules(&$angularModules) {
  _action_provider_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function action_provider_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _action_provider_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function action_provider_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function action_provider_civicrm_navigationMenu(&$menu) {
  _action_provider_civix_insert_navigation_menu($menu, NULL, array(
    'label' => E::ts('The Page'),
    'name' => 'the_page',
    'url' => 'civicrm/the-page',
    'permission' => 'access CiviReport,access CiviContribute',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _action_provider_civix_navigationMenu($menu);
} // */
