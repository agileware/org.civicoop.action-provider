<?php
/**
 * @author Klaas Eikelboom <klaas.eikelboom@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;
use Civi\ActionProvider\Exception\ExecutionException;
use CRM_ActionProvider_ExtensionUtil as E;

class CiviCase {

  public static function relationship($relType, $relContactID, $caseID, $originalCid = NULL, $relationshipID = NULL ) {

    if (!\CRM_Case_BAO_Case::accessCase($caseID)) {
      throw new ExecutionException(E::ts('No access to case with %1',[1 => $caseID]));
    }

    $ret = ['is_error' => 0];

    [$relTypeId, $b, $a] = explode('_', $relType);

    if ($relationshipID && $originalCid) {
      \CRM_Case_BAO_Case::endCaseRole($caseID, $a, $originalCid, $relTypeId);
    }

    $clientList = \CRM_Case_BAO_Case::getCaseClients($caseID);

    // Loop through multiple case clients
    foreach ($clientList as $i => $sourceContactID) {
      try {
        $params = [
          'case_id' => $caseID,
          'relationship_type_id' => $relTypeId,
          "contact_id_$a" => $relContactID,
          "contact_id_$b" => $sourceContactID,
          'sequential' => TRUE,
        ];
        // first check if there is any existing relationship present with same parameters.
        // If yes then update the relationship by setting active and start date to current time
        $relationship = \civicrm_api3('Relationship', 'get', $params)['values'];
        $params = array_merge(\CRM_Utils_Array::value(0, $relationship, $params), [
          'start_date' => 'now',
          'is_active' => TRUE,
          'end_date' => '',
        ]);
        $result = \civicrm_api3('relationship', 'create', $params);
      }
      catch (\CRM_Core_Exception $e) {
        $ret['is_error'] = 1;
        $ret['error_message'] = $e->getMessage();
      }
      // Save activity only for the primary (first) client
      if ($i == 0 && empty($result['is_error'])) {
        \CRM_Case_BAO_Case::createCaseRoleActivity($caseID, $result['id'], $relContactID, $sourceContactID);
      }
    }
    return $ret;
  }
}
