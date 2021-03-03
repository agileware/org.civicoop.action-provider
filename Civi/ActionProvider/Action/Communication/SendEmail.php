<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Communication;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\FileSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class SendEmail extends AbstractAction {

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
    $mailer = new \Civi\ActionProvider\Utils\SendEmail();
    if ($this->configuration->getParameter('use_sender_as') == 'from' && $parameters->doesParameterExists('sender_contact_id')) {
      $mailer->setSenderContactId($parameters->getParameter('sender_contact_id'), false, true);
    } elseif ($this->configuration->getParameter('use_sender_as') == 'reply_to' && $parameters->doesParameterExists('sender_contact_id')) {
      $mailer->setSenderContactId($parameters->getParameter('sender_contact_id'), true, false);
    }

    $extra_data = array();
    if ($parameters->doesParameterExists('participant_id')) {
      $extra_data['participant']['id'] = $parameters->getParameter('participant_id');;
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

    $contact_id = array($parameters->getParameter('contact_id'));
    $subject = $parameters->getParameter('subject');
    $body_text = '';
    if ($parameters->doesParameterExists('body_text')) {
      $body_text = $parameters->getParameter('body_text');
    }
    $body_html = $parameters->getParameter('body_html');
    $cc = $this->configuration->getParameter('cc');
    $bcc = $this->configuration->getParameter('bcc');
    if ($parameters->doesParameterExists('attachments')) {
      foreach($parameters->getParameter('attachments') as $fileId) {
        try {
          $file = civicrm_api3('File', 'getsingle', ['id' => $fileId]);
          $filename = \CRM_Utils_File::cleanFileName($file['uri']);
          $config = \CRM_Core_Config::singleton();
          $path = $config->customFileUploadDir . DIRECTORY_SEPARATOR . $file['uri'];
          $mailer->addAttachment($path, $filename, $file['mime_type']);
        } catch (\CiviCRM_API3_Exception $ex) {
          // Do nothing.
        }
      }
    }
    $mailer->send($contact_id, $subject, $body_text, $body_html, $extra_data, $cc, $bcc);
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
    return new SpecificationBag(array(
      new Specification('use_sender_as', 'String', E::ts('Use Sender Contact ID as'), true, 'none', null, $sender_options),
      new Specification('cc', 'String', E::ts('CC'), false),
      new Specification('bcc', 'String', E::ts('BCC'), false),
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
      new Specification('body_text', 'String', E::ts('Plain text Body'), false),
      new Specification('sender_contact_id', 'Integer', E::ts('Sender Contact ID'), false),
      new Specification('activity_id', 'Integer', E::ts('Activity ID'), false),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), false),
      new Specification('case_id', 'Integer', E::ts('Case ID'), false),
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), false),
      new Specification('attachments', 'Integer', E::ts('Attachment(s)'), false, null, null, null, true)
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
