<?php

namespace Civi\ActionProvider;

use Civi\API\Event\PrepareEvent;

/**
 * API Wrapper class for intercepting API calls inside of Actions
 */
class APIWrapper {

  /**
   * Listener for civi.api.prepare event
   * Adds callback wrapper to alter API calls
   *
   * @param \Civi\API\Event\PrepareEvent $event
   *
   * @return void
   */
  public static function onApiPrepare (PrepareEvent $event): void {
    $api_request = $event->getApiRequest();

    if (is_array($api_request) && $api_request['version'] == 3) {
      $action = $api_request['action'];
      if($action == 'get' || $action == 'getsingle') {
        // Only interested in APIv3 requests
        $event->wrapAPI([self::class, 'wrapV3calls']);
      }
    }
  }

  /**
   * Wrapper callback. Executes mutators then calls the API function
   *
   * @param array $api_request
   * @param array $forward
   *
   * @return mixed
   */
  public static function wrapV3calls(array $api_request, array $forward) {
    self::checkIsDeleted($api_request);

    return $forward($api_request);
  }

  /**
   * Mutator to avoid pulling deleted entities where behaviour is not specified.
   *
   * @param array $api_request
   *
   * @return void
   */
  protected static function checkIsDeleted(array &$api_request): void{
    // Contacts, Activities, and Cases can be marked as deleted, so check those directly
    if (($api_request['entity'] === 'Contact'
      || $api_request['entity'] === 'Activity'
      || $api_request['entity'] === 'Case')
      && !array_key_exists('is_deleted', $api_request['params'])) {
      $api_request['params']['is_deleted'] = 0;
    }

    // Other entities might be dependent on an attached entity that can be marked as deleted
    // This will *normally* be a contact, but check for activities and cases as well
    foreach(['contact_id', 'activity_id', 'case_id'] as $entityParam) {
      if (!empty($api_request['fields'][$entityParam])
        && !array_key_exists("{$entityParam}.is_deleted", $api_request['params'])) {
        $api_request['params']["{$entityParam}.is_deleted"] = 0;
      }
    }
  }
}