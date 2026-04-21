<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

use Civi\Token\TokenProcessor;

class Tokens {

  /**
   * Returns a processed message. Meaning that all tokens are replaced with their value.
   * This message could then be used to generate the PDF.
   *
   * @param $contactId
   * @param $message
   * @param array $contactData
   * @param string $format either 'text/html' or 'text/plain'.
   *
   * @return string
   */
  public static function replaceTokens($contactId, $message, $contactData=array(), $format='text/html') {
    $schema[] = 'contactId';
    $context['contactId'] = $contactId;
    foreach ($contactData['extra_data'] ?? [] as $entity => $entityData) {
      $schema[] = "{$entity}Id";
      if (isset($entityData['id'])) {
        $context["{$entity}Id"] = $entityData['id'];
      } else {
        $context["{$entity}Id"] = $contactData["{$entity}_id"];
      }
      $context[$entity] = $entityData;
    }
    if (!empty($contactData['case_id'])) {
      $schema[] = "caseId";
      $context["caseId"] = $contactData['case_id'];
    }
    if (!empty($contactData['contribution_id'])) {
      $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $contactData['contribution_id']]);
      $schema[] = "contributionId";
      $context["contributionId"] = $contactData['contribution_id'];
      $context["contribution"] = $contribution;
    }
    if (!empty($contactData['activity_id'])) {
      $activity = civicrm_api3('Activity', 'getsingle', ['id' => $contactData['activity_id']]);
      $schema[] = "activityId";
      $context["activityId"] = $contactData['activity_id'];
      $context["activity"] = $activity;
    }
    if (!empty($contactData['membership_id'])) {
      $membership = civicrm_api3('Membership', 'getsingle', ['id' => $contactData['membership_id']]);
      $schema[] = "membershipId";
      $context["membershipId"] = $contactData['membership_id'];
      $context["membership"] = $membership;
    }

    // Whether to enable Smarty evaluation.
    $useSmarty = (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY);

    $tokenProcessor = new TokenProcessor(\Civi::dispatcher(), [
      'controller' => __CLASS__,
      'schema' => $schema,
      'smarty' => $useSmarty,
    ]);

    // Populate the token processor.
    $tokenProcessor->addMessage('message', $message, $format);
    $row = $tokenProcessor->addRow($context);
    // Evaluate and render.
    $tokenProcessor->evaluate();
    return $row->render('message');
  }

  /**
   *  Copy from \CRM_Utils_Token.
   *  Reason it has to call the local self::getApiTokenReplacement
   *
   * @param $entity
   * @param $entityArray
   * @param $str
   * @param array $knownTokens
   * @param false $escapeSmarty
   *
   * @return array|mixed|string|string[]|null
   * @throws \CRM_Core_Exception
   */
  public static function replaceEntityTokens($entity, $entityArray, $str, $knownTokens = [], $escapeSmarty = FALSE) {
    if (!$knownTokens || empty($knownTokens[$entity])) {
      return $str;
    }

    foreach ($knownTokens[$entity] as $token) {
      $replacement = self::getApiTokenReplacement($entity, $token, $entityArray);
      if ($escapeSmarty) {
        $replacement = \CRM_Utils_Token::tokenEscapeSmarty($replacement);
      }
      $str = str_replace('{' . $entity . '.' . $token . '}', $replacement, $str);
    }
    return preg_replace('/\\\\|\{(\s*)?\}/', ' ', $str);
  }

  /**
   * Generic function for formatting token replacement for an api field
   * This function is copied from \CRM_Utils_Token.
   * Motivation
   *  the dataprocessor does not have the getfield function (only getfields)
   *  and the getfields method asks for 'api_action' instead of 'action'
   * @param string $entity
   * @param string $token
   * @param array $entityArray
   * @return string
   * @throws \CRM_Core_Exception
   */
  public static function getApiTokenReplacement($entity, $token, $entityArray) {
    if (!isset($entityArray[$token])) {
      return '';
    }
    $fields = civicrm_api3($entity, 'getfields', ['api_action' => 'get', 'name' => $token, 'get_options' => 'get']);
    if(!key_exists($token,$fields['values'])){
      throw new \CRM_Core_Exception ("The field '{$token}' doesn't exist.");
    }
    $field = $fields['values'][$token];
    $fieldType = $field['type'] ?? NULL;
    // Boolean fields
    if ($fieldType == \CRM_Utils_Type::T_BOOLEAN && empty($field['options'])) {
      $field['options'] = [ts('No'), ts('Yes')];
    }
    // Match pseudoconstants
    if (!empty($field['options'])) {
      $ret = [];
      foreach ((array) $entityArray[$token] as $val) {
        $ret[] = $field['options'][$val];
      }
      return implode(', ', $ret);
    }
    // Format date fields
    elseif ($entityArray[$token] && $fieldType == \CRM_Utils_Type::T_DATE) {
      return \CRM_Utils_Date::customFormat($entityArray[$token]);
    }
    return implode(', ', (array) $entityArray[$token]);
  }

}
