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
    $version = \CRM_Core_BAO_Domain::version();
    if (version_compare('5.42', $version, '>=')) {
      // When civicrm prior to version 5.42 use the old way of handling tokens.
      return self::replaceTokensWithDeprecatedMethod($contactId, $message, $contactData);
    }
    $schema[] = 'contactId';
    $context['contactId'] = $contactId;
    foreach ($contactData['extra_data'] ?? [] as $entity => $entityData) {
      $schema[] = "{$entity}Id";
      $context["{$entity}Id"] = $contactData["{$entity}_id"];
      $context[$entity] = $entityData;
    }
    if (isset($contactData['case_id']) && !empty($contactData['case_id'])) {
      $schema[] = "caseId";
      $context["caseId"] = $contactData['case_id'];
    }
    if (isset($contactData['contribution_id']) && !empty($contactData['contribution_id'])) {
      $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $contactData['contribution_id']]);
      $schema[] = "contributionId";
      $context["contributionId"] = $contactData['contribution_id'];
      $context["contribution"] = $contribution;
    }
    if (isset($contactData['activity_id']) && !empty($contactData['activity_id'])) {
      $activity = civicrm_api3('Activity', 'getsingle', ['id' => $contactData['activity_id']]);
      $schema[] = "activityId";
      $context["activityId"] = $contactData['activity_id'];
      $context["activity"] = $activity;
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
   * Returns a processed message. Meaning that all tokens are replaced with their value.
   * This message could then be used to generate the PDF.
   *
   * This method is to keep compatibility with CiviCRM prior to version 5.42
   *
   * @param $contactId
   * @param $message
   * @param array $contactData
   *
   * @return string
   */
  public static function replaceTokensWithDeprecatedMethod($contactId, $message, $contactData=array()) {
    if (isset($contactData['activity_id']) && !empty($contactData['activity_id'])) {
      $contactData['extra_data']['activity']['id'] = $contactData['activity_id'];
    }

    $tokenCategories = self::getTokenCategories();
    $messageTokens = \CRM_Utils_Token::getTokens($message);
    $returnProperties = [
      'sort_name' => 1,
      'email' => 1,
      'address' => 1,
      'do_not_email' => 1,
      'is_deceased' => 1,
      'on_hold' => 1,
      'display_name' => 1,
    ];
    if (isset($messageTokens['contact']) && is_array($messageTokens['contact'])) {
      foreach ($messageTokens['contact'] as $prop) {
        $returnProperties[$prop] = 1;
      }
    }

    $contact = self::getTokenDetails($contactId, $returnProperties);
    foreach($contactData as $key => $val) {
      $contact[$key] = $val;
    }

    $contactHookArray[$contactId] = $contact;
    \CRM_Utils_Hook::tokenValues($contactHookArray, [$contactId], NULL, $messageTokens);
    // Now update the original array.
    $contact = $contactHookArray[$contactId];

    $domainId = \CRM_Core_BAO_Domain::getDomain();
    $tokenHtml = \CRM_Utils_Token::replaceDomainTokens($message, $domainId, TRUE, $messageTokens, TRUE);
    $tokenHtml = \CRM_Utils_Token::replaceContactTokens($tokenHtml, $contact, FALSE, $messageTokens, FALSE, TRUE);
    $tokenHtml = \CRM_Utils_Token::replaceComponentTokens($tokenHtml, $contact, $messageTokens, TRUE);
    $tokenHtml = \CRM_Utils_Token::replaceHookTokens($tokenHtml, $contact, $tokenCategories, TRUE);
    if (isset($contactData['case_id']) && !empty($contactData['case_id'])) {
      $tokenHtml = \CRM_Utils_Token::replaceCaseTokens($contactData['case_id'], $tokenHtml, $messageTokens);
    }
    if (isset($contactData['contribution_id']) && !empty($contactData['contribution_id'])) {
      $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $contactData['contribution_id']]);
      $tokenHtml = \CRM_Utils_Token::replaceContributionTokens($tokenHtml, $contribution, TRUE, $messageTokens);
    }
    if (isset($contactData['activity_id']) && !empty($contactData['activity_id'])) {
      $activity = civicrm_api3('Activity', 'getsingle', ['id' => $contactData['activity_id']]);
      $tokenHtml = \CRM_Utils_Token::replaceEntityTokens('activity', $activity, $tokenHtml, $messageTokens);
    }
    \CRM_Utils_Token::replaceGreetingTokens($tokenHtml, NULL, $contactId);

    if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY) {
      $smarty = \CRM_Core_Smarty::singleton();
      // also add the contact tokens to the template
      $smarty->assign_by_ref('contact', $contact);
      $tokenHtml = $smarty->fetch("string:$tokenHtml");
    }

    return $tokenHtml;
  }

  /**
   * Get the categories required for rendering tokens.
   *
   * @return array
   */
  protected static function getTokenCategories() {
    if (!isset(\Civi::$statics[__CLASS__]['token_categories'])) {
      $tokens = array();
      \CRM_Utils_Hook::tokens($tokens);
      \Civi::$statics[__CLASS__]['token_categories'] = array_keys($tokens);
    }
    return \Civi::$statics[__CLASS__]['token_categories'];
  }

  protected static function getTokenDetails($contact_id, $returnProperties=NULL) {
    $params = [];
    $params[] = [
      \CRM_Core_Form::CB_PREFIX . $contact_id,
      '=',
      1,
      0,
      0,
    ];
    if (empty($returnProperties)) {
      $fields = array_merge(array_keys(\CRM_Contact_BAO_Contact::exportableFields()),
        ['display_name', 'checksum', 'contact_id']
      );
      foreach ($fields as $val) {
        // The unavailable fields are not available as tokens, do not have a one-2-one relationship
        // with contacts and are expensive to resolve.
        // @todo see CRM-17253 - there are some other fields (e.g note) that should be excluded
        // and upstream calls to this should populate return properties.
        $unavailableFields = ['group', 'tag'];
        if (!in_array($val, $unavailableFields)) {
          $returnProperties[$val] = 1;
        }
      }
    }

    $custom = [];
    foreach ($returnProperties as $name => $dontCare) {
      $cfID = \CRM_Core_BAO_CustomField::getKeyID($name);
      if ($cfID) {
        $custom[] = $cfID;
      }
    }

    $details = \CRM_Contact_BAO_Query::apiQuery($params, $returnProperties, NULL, NULL, 0, 1, TRUE, FALSE, TRUE, \CRM_Contact_BAO_Query::MODE_CONTACTS, NULL, TRUE);
    $contactDetails = &$details[0];
    if (array_key_exists($contact_id, $contactDetails)) {
      if (!empty($contactDetails[$contact_id]['preferred_communication_method'])
      ) {
        $communicationPreferences = [];
        foreach ($contactDetails[$contact_id]['preferred_communication_method'] as $val) {
          if ($val) {
            $communicationPreferences[$val] = \CRM_Core_PseudoConstant::getLabel('CRM_Contact_DAO_Contact', 'preferred_communication_method', $val);
          }
        }
        $contactDetails[$contact_id]['preferred_communication_method'] = implode(', ', $communicationPreferences);
      }

      foreach ($custom as $cfID) {
        if (isset($contactDetails[$contact_id]["custom_{$cfID}"])) {
          $contactDetails[$contact_id]["custom_{$cfID}"] = \CRM_Core_BAO_CustomField::displayValue($contactDetails[$contact_id]["custom_{$cfID}"], $cfID);
        }
      }

      // special case for greeting replacement
      foreach ([
                 'email_greeting',
                 'postal_greeting',
                 'addressee',
               ] as $val) {
        if (!empty($contactDetails[$contact_id][$val])) {
          $contactDetails[$contact_id][$val] = $contactDetails[$contact_id]["{$val}_display"];
        }
      }
    }
    return $contactDetails[$contact_id];
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
   * @throws \CiviCRM_API3_Exception
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
   * @throws \CiviCRM_API3_Exception
   */
  public static function getApiTokenReplacement($entity, $token, $entityArray) {
    if (!isset($entityArray[$token])) {
      return '';
    }
    $fields = civicrm_api3($entity, 'getfields', ['api_action' => 'get', 'name' => $token, 'get_options' => 'get']);
    if(!key_exists($token,$fields['values'])){
      throw new \CiviCRM_API3_Exception ("The field '{$token}' doesn't exist.");
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
