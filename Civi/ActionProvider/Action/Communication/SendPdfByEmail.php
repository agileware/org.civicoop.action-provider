<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Communication;

use Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\Tokens;
use CRM_ActionProvider_ExtensionUtil as E;

class SendPdfByEmail extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $participantId = $parameters->getParameter('participant_id');
    $contactId = $parameters->getParameter('contact_id');
    $pdf_message = $parameters->getParameter('pdf_message');
    $filename = $this->configuration->getParameter('filename').'.pdf';
    $_fullPathName =  \CRM_Utils_File::tempnam();
    $contact = [];
    if ($participantId) {
      $contact['extra_data']['participant']['id'] = $participantId;
    }

    $processedMessage = Tokens::replaceTokens($contactId, $pdf_message, $contact);
    if ($processedMessage === false) {
      return;
    }
    //time being hack to strip '&nbsp;'
    //from particular letter line, CRM-6798
    \CRM_Contact_Form_Task_PDFLetterCommon::formatMessage($processedMessage);
    $text = array($processedMessage);
    $pdfContents = \CRM_Utils_PDF_Utils::html2pdf($text, $filename, TRUE);
    file_put_contents($_fullPathName, $pdfContents);

    $mailer = new \Civi\ActionProvider\Utils\SendEmail();
    $mailer->addAttachment($_fullPathName, $filename, 'application/pdf');
    if ($this->configuration->getParameter('use_sender_as') == 'from' && $parameters->doesParameterExists('sender_contact_id')) {
      $mailer->setSenderContactId($parameters->getParameter('sender_contact_id'), false, true);
    } elseif ($this->configuration->getParameter('use_sender_as') == 'from' && $parameters->doesParameterExists('sender_contact_id')) {
      $mailer->setSenderContactId($parameters->getParameter('sender_contact_id'), true, false);
    }

    if ($parameters->doesParameterExists('case_id')) {
      $mailer->setCaseId($parameters->getParameter('case_id'));
    }
    if ($parameters->doesParameterExists('contribution_id')) {
      $mailer->setContributionId($parameters->getParameter('contribution_id'));
    }
    if ($parameters->doesParameterExists('activity_id')) {
      $mailer->setActivityId($parameters->getParameter('activity_id'));
    }
    if ($parameters->doesParameterExists('participant_id')) {
      $mailer->setParticipantId($parameters->getParameter('participant_id'));
    }

    $mailer->send(array($contactId), $parameters->getParameter('subject'), $parameters->getParameter('body_text'), $parameters->getParameter('body_html'));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $sender_options = array(
      'from' => E::ts('Send E-mail from E-mail adress of Sender Contact ID'),
      'reply_to' => E::ts('Set E-mail address of Sender Contact ID as Reply To'),
      'none' => E::ts('Do not use Sender Contact ID')
    );

    $filename = new Specification('filename', 'String', E::ts('Filename'), true, E::ts('document'));
    $filename->setDescription(E::ts('Without the extension .pdf or .zip'));

    return new SpecificationBag(array(
      $filename,
      new Specification('use_sender_as', 'String', E::ts('Use Sender Contact ID as'), true, 'none', null, $sender_options),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Receiver Contact ID'), true),
      new Specification('subject', 'String', E::ts('Subject'), true),
      new Specification('body_html', 'String', E::ts('HTML Body'), true),
      new Specification('body_text', 'String', E::ts('Plain text Body'), true),
      new Specification('pdf_message', 'String', E::ts('PDF Message'), true),
      new Specification('sender_contact_id', 'Integer', E::ts('Sender Contact ID'), false),
      new Specification('activity_id', 'Integer', E::ts('Activity ID'), false),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), false),
      new Specification('case_id', 'Integer', E::ts('Case ID'), false),
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), false),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }

}