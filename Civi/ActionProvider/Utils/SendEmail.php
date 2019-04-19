<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils;

class SendEmail {

  private $from_name;

  private $from_email;

  private $reply_to_email;

  private $sender_contact_id;

  private $case_id;

  private $contribution_id;

  private $activity_id;

  public function __construct($from_email=null, $from_name=null) {
    $this->from_email = $from_email;
    $this->from_name = $from_name;
  }

  public function  setCaseId($case_id) {
    $this->case_id = $case_id;
  }

  public function setContributionId($contribution_id) {
    $this->contribution_id = $contribution_id;
  }

  public function setActivityId($activity_id) {
    $this->activity_id = $activity_id;
  }

  /**
   * Set the sender contact ID. The sender contact ID is used as the source contact ID
   * for the e-mail activity.
   * The e-mail address of the sender is used as ReplyTo
   * Alternatively you could also set From Header
   *
   *
   * @param int $senderContactId
   * @param bool $useAsReplyTo
   * @param bool $useAsFrom
   */
  public function setSenderContactId($senderContactId, $useAsReplyTo=true, $useAsFrom=false) {
    try {
      $senderContact = civicrm_api3('Contact', 'getsingle', ['id' => $senderContactId]);
      $this->sender_contact_id = $senderContactId;
      if ($senderContact['email']) {
        if ($useAsReplyTo) {
          $this->reply_to_email = $senderContact['email'];
        }
        if ($useAsFrom) {
          $this->from_email = $senderContact['email'];
          if ($senderContact['display_name'] != $senderContact['email']) {
            $this->from_name = $senderContact['display_name'];
          }
        }
      }
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  /**
   * Send e-mail
   *
   * @param $contactIds
   * @param $subject
   * @param $body_text
   * @param $body_html
   * @param bool $extra_data
   *
   * @return array
   * @throws \Exception
   */
  public function send($contactIds, $subject, $body_text, $body_html, $extra_data=false) {
    $from = \CRM_Core_BAO_Domain::getNameAndEmail();
    $from = "$from[0] <$from[1]>";
    if ($this->from_email && $this->from_name) {
      $from = $this->from_name."<".$this->from_email.">";
    } elseif ($this->from_email) {
      $from = $this->from_email;
    }

    $domain     = \CRM_Core_BAO_Domain::getDomain();
    $result     = NULL;
    if (!$body_text) {
      $body_text = \CRM_Utils_String::htmlToText($body_html);
    }

    $returnValues = array();
    foreach($contactIds as $contactId) {
      $contact_params = array(array('contact_id', '=', $contactId, 0, 0));
      list($contact, $_) = \CRM_Contact_BAO_Query::apiQuery($contact_params);

      //CRM-4524
      $contact = reset($contact);

      if (!$contact || is_a($contact, 'CRM_Core_Error')) {
        throw new \Exception('Could not find contact with ID: ' . $contact_params['contact_id']);
      }

      //CRM-5734

      // get tokens to be replaced
      $tokens = array_merge_recursive(\CRM_Utils_Token::getTokens($body_text),
        \CRM_Utils_Token::getTokens($body_html),
        \CRM_Utils_Token::getTokens($subject));

      if ($this->case_id) {
        $contact['case_id'] = $this->case_id;
      }
      if ($this->contribution_id) {
        $contact['contribution_id'] = $this->contribution_id;
      }
      if ($this->activity_id) {
        $contact['activity_id'] = $this->activity_id;
      }
      if ($extra_data) {
        $contact['extra_data'] = $extra_data;
      }

      if ($contact['do_not_email'] || empty($contact['email']) || \CRM_Utils_Array::value('is_deceased', $contact) || $contact['on_hold']) {
        /**
         * Contact is deceased or has opted out from mailings so do not send the e-mail
         */
        continue;
      } else {
        /**
         * Send e-mail to the contact
         */
        $email = $contact['email'];
        $toName = $contact['display_name'];
      }

      \CRM_Utils_Hook::tokenValues($contact, $contact['contact_id'], NULL, $tokens);
      // call token hook
      $hookTokens = array();
      \CRM_Utils_Hook::tokens($hookTokens);
      $categories = array_keys($hookTokens);

      // do replacements in text and html body
      $type = array('html', 'text');
      foreach ($type as $key => $value) {
        $bodyType = "body_{$value}";
        if ($$bodyType) {
          if ($this->contribution_id) {
            try {
              $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $this->contribution_id]);
              $$bodyType = \CRM_Utils_Token::replaceContributionTokens($$bodyType, $contribution, TRUE, $tokens);
            } catch (\Exception $e) {
              // Do nothing
            }
          }

          $$bodyType = \CRM_Utils_Token::replaceDomainTokens($$bodyType, $domain, TRUE, $tokens, TRUE);
          $$bodyType = \CRM_Utils_Token::replaceHookTokens($$bodyType, $contact, $categories, TRUE);
          \CRM_Utils_Token::replaceGreetingTokens($$bodyType, $contact, $contact['contact_id']);
          $$bodyType = \CRM_Utils_Token::replaceContactTokens($$bodyType, $contact, FALSE, $tokens, FALSE, TRUE);
          $$bodyType = \CRM_Utils_Token::replaceComponentTokens($$bodyType, $contact, $tokens, TRUE);
        }
      }
      $html = $body_html;
      $text = $body_text;
      if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY) {
        $smarty = \CRM_Core_Smarty::singleton();
        foreach ($type as $elem) {
          $$elem = $smarty->fetch("string:{$$elem}");
        }
      }

      // do replacements in message subject
      $messageSubject = \CRM_Utils_Token::replaceContactTokens($subject, $contact, false, $tokens);
      $messageSubject = \CRM_Utils_Token::replaceDomainTokens($messageSubject, $domain, true, $tokens);
      $messageSubject = \CRM_Utils_Token::replaceComponentTokens($messageSubject, $contact, $tokens, true);
      $messageSubject = \CRM_Utils_Token::replaceHookTokens($messageSubject, $contact, $categories, true);

      if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY) {
        $messageSubject = $smarty->fetch("string:{$messageSubject}");
      }

      // set up the parameters for CRM_Utils_Mail::send
      $mailParams = array(
        'groupName' => 'E-mail from API',
        'from' => $from,
        'toName' => $toName,
        'toEmail' => $email,
        'subject' => $messageSubject,
      );

      if (!$html || $contact['preferred_mail_format'] == 'Text' || $contact['preferred_mail_format'] == 'Both') {
        // render the &amp; entities in text mode, so that the links work
        $mailParams['text'] = str_replace('&amp;', '&', $text);
      }
      if ($html && ($contact['preferred_mail_format'] == 'HTML' || $contact['preferred_mail_format'] == 'Both')) {
        $mailParams['html'] = $html;
      }
      if ($this->from_email) {
        $mailParams['replyTo'] = $this->reply_to_email;
      }
      $result = \CRM_Utils_Mail::send($mailParams);
      if (!$result) {
        throw new \Exception('Error sending e-mail to ' . $contact['display_name'] . ' <' . $email . '> ');
      }

      //create activity for sending e-mail.
      $activityTypeID = \CRM_Core_OptionGroup::getValue('activity_type', 'Email', 'name');

      // CRM-6265: save both text and HTML parts in details (if present)
      if ($html and $text) {
        $details = "-ALTERNATIVE ITEM 0-\n$html\n-ALTERNATIVE ITEM 1-\n$text\n-ALTERNATIVE END-\n";
      }
      else {
        $details = $html ? $html : $text;
      }

      $activityParams = array(
        'source_contact_id' => $contactId,
        'activity_type_id' => $activityTypeID,
        'activity_date_time' => date('YmdHis'),
        'subject' => $messageSubject,
        'details' => $details,
        'status_id' => "Completed",
      );
      if ($this->sender_contact_id) {
        $activityParams['source_contact_id'] = $this->sender_contact_id;
      }
      $activity = \CRM_Activity_BAO_Activity::create($activityParams);

      $activityContacts = \CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
      $targetID = \CRM_Utils_Array::key('Activity Targets', $activityContacts);

      $activityTargetParams = array(
        'activity_id' => $activity->id,
        'contact_id' => $contactId,
        'record_type_id' => $targetID
      );
      \CRM_Activity_BAO_ActivityContact::create($activityTargetParams);

      if (!empty($this->case_id)) {
        $caseActivity = array(
          'activity_id' => $activity->id,
          'case_id' => $this->case_id,
        );
        \CRM_Case_BAO_Case::processCaseActivity($caseActivity);
      }

      $returnValues[$contactId] = array(
        'contact_id' => $contactId,
        'send' => 1,
        'status_msg' => 'Succesfully send e-mail to ' . ' <' . $email . '> ',
      );
    }
    return $returnValues;
  }

}