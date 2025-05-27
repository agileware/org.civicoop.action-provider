<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class SendParticipantRegistrationMail extends AbstractAction {

  private static $profiles;

  public function __construct() {
    if (!self::$profiles) {
      $profiles = civicrm_api3('UFGroup', 'get', ['is_active' => 1, 'options' => ['limit' => 0]]);
      foreach($profiles['values'] as $profile) {
        self::$profiles[$profile['name']] = $profile['title'];
      }
    }
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $template = \CRM_Core_Smarty::singleton();
    $participantId = $parameters->getParameter('participant_id');
    $participant = civicrm_api3('Participant','getsingle',['id' => $participantId]);
    $event =  civicrm_api3('Event','getsingle',['id' => $participant['event_id']]);
    $event['is_email_confirm'] = true; // Hack to enable sending of e-mail
    $event['confirm_from_name'] = $this->configuration->getParameter('from_name');
    $event['confirm_from_email'] = $this->configuration->getParameter('from_email');
    $custom_pre_id = null;
    if ($this->configuration->getParameter('profile')) {
      try {
        $profile = civicrm_api3('UFGroup', 'getsingle', ['name' => $this->configuration->getParameter('profile')]);
        $custom_pre_id = $profile['id'];
      } catch (\CiviCRM_API3_Exception $ex) {
        // Do nothing.
      }
    }


    $fromEmails = \CRM_Event_BAO_Event::getFromEmailIds($event['id']);
    $contactID = $participant['contact_id'];

    [$displayName, $email] = \CRM_Contact_BAO_Contact_Location::getEmailDetails($contactID);
    $notifyEmail = \CRM_Utils_Array::valueByRegexKey('/^email-/', $participant) ?? $email;
    if (!empty($notifyEmail)) {
      $contributionID = \CRM_Core_DAO::getFieldValue('CRM_Event_DAO_ParticipantPayment', $participantId, 'contribution_id', 'participant_id');

      $profilePre = \CRM_Event_BAO_Event::buildCustomDisplay($custom_pre_id,
        'customPre',
        $contactID,
        $template,
        $participantId,
        false,
        TRUE,
        $participant
      );

      $tplParams = array_merge($participant, [
        'email' => $notifyEmail,
        'confirm_email_text' => $event['confirm_email_text'] ?? NULL,
        'isShowLocation' => $event['is_show_location'] ?? NULL,
        'customPre' => $profilePre[0],
        'customPre_grouptitle' => empty($profilePre[1]) ? NULL : [\CRM_Core_BAO_UFGroup::getFrontEndTitle((int) $custom_pre_id)],
        'customPost' => null,
        'customPost_grouptitle' => null,
        'participantID' => $participantId,
        'contactID' => $contactID,
        'participant' => ['id' => $participantId],
        'event' => ['id' => $event['id']],
        'credit_card_number' => \CRM_Utils_System::mungeCreditCard(\CRM_Utils_Array::value('credit_card_number', $participant)),
        'credit_card_exp_date' => \CRM_Utils_Date::mysqlToIso(\CRM_Utils_Date::format(\CRM_Utils_Array::value('credit_card_exp_date', $participant))),
        'selfcancelxfer_time' => abs($event['selfcancelxfer_time']),
        'selfservice_preposition' => $event['selfcancelxfer_time'] < 0 ? ts('after') : ts('before'),
        'currency' => $event['currency'] ?? \CRM_Core_Config::singleton()->defaultCurrency,
      ]);

      $sendTemplateParams = [
        'workflow' => 'event_online_receipt',
        'contactId' => $contactID,
        'tplParams' => $tplParams,
        'isTest' => false,
        'PDFFilename' => ts('confirmation') . '.pdf',
        'modelProps' => [
          'participantID' => $participantId,
          'eventID' => $event['id'],
        ],
      ];
      if ($contributionID) {
        $sendTemplateParams['modelProps']['contributionID'] = $contributionID;
      }

      $sendTemplateParams['from'] = $event['confirm_from_email'];
      $sendTemplateParams['toName'] = $displayName;
      $sendTemplateParams['toEmail'] = $notifyEmail;
      $sendTemplateParams['cc'] = $fromEmails['cc'] ?? NULL;
      $sendTemplateParams['bcc'] = $fromEmails['bcc'] ?? NULL;

      //send email with pdf invoice
      if (\Civi::settings()->get('invoice_is_email_pdf')) {
        $sendTemplateParams['isEmailPdf'] = TRUE;
        $sendTemplateParams['contributionId'] = $contributionID;
      }
      [$mailSent, $subject, $body, $html] = \CRM_Core_BAO_MessageTemplate::sendTemplate($sendTemplateParams);
      if ($mailSent) {
        if ($contributionID) {
          \Civi\Api4\Contribution::update(FALSE)
            ->addWhere('id', '=', $contributionID)
            ->setValues(['receipt_date' => 'now'])
            ->execute();
        }

        $objParticipant = new \CRM_Event_BAO_Participant();
        $objParticipant->id = $participantId;
        $objParticipant->find(TRUE);

        $activityParams['subject'] = $subject;
        $activityParams['details'] = $html;

        \CRM_Activity_BAO_Activity::addActivity($objParticipant, 'Email', $contactID, $activityParams);
      }
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('from_name', 'String', E::ts('From Name'), true),
      new Specification('from_email', 'String', E::ts('From Email'), true),
      new Specification('profile', 'String', E::ts('Profile'), false, null, null, self::$profiles),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true),
    ]);
  }


}
