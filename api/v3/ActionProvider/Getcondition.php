<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * ActionProvider.Getcondition API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_action_provider_getcondition($params) {
  $container = \Civi::service('action_provider');
  if (isset($params['context'])) {
    $provider = $container->getProviderByContext($params['context']);
  } else {
    $provider = $container->getDefaultProvider();
  }
  $condition = $provider->getConditionByName($params['name']);
  return $condition->toArray();
}

/**
 * ActionProvider.Getcondition API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_action_provider_getcondition_spec(&$spec) {
  $spec['name'] = array(
    'title' => E::ts('Name'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => true
  );
  $spec['context'] = array(
    'title' => E::ts('Context'),
    'type' => CRM_Utils_Type::T_STRING,
    'api.required' => false
  );
}