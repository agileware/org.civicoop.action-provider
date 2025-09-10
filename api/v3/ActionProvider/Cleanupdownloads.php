<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

/**
 * ActionProvider.Getaction API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_action_provider_cleanupdownloads($params) {
  $date = date('Y-m-d H:i:s');
  $actionProviderDownloads = \Civi\Api4\ActionProviderDownload::get(FALSE)
  ->addWhere('expire_date', '<', $date)
  ->setLimit(25)
  ->execute();
  $count = 0;
  foreach($actionProviderDownloads as $actionProviderDownload) {
    \Civi\Api4\ActionProviderDownload::delete(FALSE)
    ->addWhere('id', '=', $actionProviderDownload['id'])
    ->execute();
    $count ++;
  }
  return [
    'count' => $count,
    'values' => [],
  ];
}

/**
 * ActionProvider.Getaction API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_action_provider_cleanupdownloads_spec(&$spec) {
}