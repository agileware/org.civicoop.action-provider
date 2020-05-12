<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

class Tokens {

  /**
   * Returns a processed message. Meaning that all tokens are replaced with their value.
   * This message could then be used to generate the PDF.
   *
   * @param $contactId
   * @param $message
   * @param array $contactData
   *
   * @return string
   */
  public static function replaceTokens($contactId, $message, $contactData=array()) {
    $tokenCategories = self::getTokenCategories();
    $messageTokens = \CRM_Utils_Token::getTokens($message);

    $contact_params = [['contact_id', '=', $contactId, 0, 0]];
    list($contact, $_) = \CRM_Contact_BAO_Query::apiQuery($contact_params);
    $contact = reset($contact);
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

}
