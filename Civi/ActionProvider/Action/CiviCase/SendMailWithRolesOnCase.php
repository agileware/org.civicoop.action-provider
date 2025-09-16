<?php

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\Api4\Relationship;
use Civi\Api4\RelationshipType;
use CRM_ActionProvider_ExtensionUtil as E;

class SendMailWithRolesOnCase extends AbstractAction {

  public const DEBUG = FALSE;

  // used for the select
  protected array $relationshipTypes = [];

  // used for id mapping
  protected array $relationshipTypeIds = [];

  public function __construct() {
    parent::__construct();

    $relationshipTypes = RelationshipType::get(FALSE)
      ->addSelect('id', 'name_a_b', 'label_a_b')
      ->addWhere('is_active', '=', TRUE)
      ->execute();
    foreach ($relationshipTypes as $relationshipType) {
      $this->relationshipTypes[$relationshipType['name_a_b']] = $relationshipType['label_a_b'];
      $this->relationshipTypeIds[$relationshipType['name_a_b']] = $relationshipType['id'];
    }
  }

  /**
   * Run the action.
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $case_id = $parameters->getParameter('case_id');
    $relationship_main = (int) $parameters->getParameter('relationship_main');

    \Civi::log()
      ->debug('SendMailWithRolesOnCase: ' . $case_id . ' - ' . print_r($relationship_main, TRUE));

    $relationship_cc = $parameters->getParameter('relationship_cc');

    \Civi::log()->debug('CC: ' . print_r($relationship_cc, TRUE));

    // formfieldlibrary returns coma-separated single value (in the first array element), so we have to convert it to an array
    if (!empty($relationship_cc) && !is_numeric($relationship_cc[0])) {
      $relationship_cc = explode(',', $relationship_cc[0]);
    }

    // get main contact
    try {
      $relationship = Relationship::get(FALSE)
        ->addSelect('contact_id_b')
        ->addWhere('relationship_type_id', '=', $relationship_main)
        ->addWhere('is_active', '=', TRUE)
        ->addWhere('case_id', '=', $case_id)
        ->execute()->first();

      if (!empty($relationship) && !empty($relationship['contact_id_b'])) {
        \Civi::log()->debug('API Output:');
        \Civi::log()->debug('contact: ' . print_r($relationship, TRUE));
        $contact_id = $relationship['contact_id_b'];
        $output->setParameter('contact_id', $contact_id);
      }
      else {
        \Civi::log()->warning('$SendMailWithRolesOnCase: No contact found for case ' . $case_id . ' and main relationship type ' . $relationship_main);
      }
    }
    catch (\Exception $e) {
      // Do nothing but log it...
      \Civi::log()->error('$SendMailWithRolesOnCase: ' . $e->getMessage());
    }

    if (empty($contact_id)) {
      \Civi::log()
        ->warning('$SendMailWithRolesOnCase: No contact found for case ' . $case_id . ' and relationship type ' . $relationship_main);
      return;
    }

    // get cc contacts (optional)
    $contact_ids_cc = [];
    try {
      $relationships = Relationship::get(FALSE)
        ->addSelect('contact_id_b')
        ->addWhere('relationship_type_id', 'IN', $relationship_cc)
        ->addWhere('is_active', '=', TRUE)
        ->addWhere('case_id', '=', $case_id)
        ->execute();
      foreach ($relationships as $relationship) {
        $id = $relationship['contact_id_b'];

        // do not send cc to main contact
        if ($id == $contact_id) {
          continue;
        }

        $contact_ids_cc[$id] = 1;
      }
    }
    catch (\Exception $e) {
      // Do nothing but log it...
      \Civi::log()->error('$SendMailWithRolesOnCase: ' . $e->getMessage());
    }

    $cc_contacts_output = [];
    $cc = FALSE;
    if (!empty($contact_ids_cc)) {
      $cc_mails = [];
      foreach ($contact_ids_cc as $contact_id_cc => $value) {
        \Civi::log()->debug('Reading cc contact: ' . $contact_id_cc);
        try {
          $contact = \Civi\Api4\Contact::get(FALSE)
            ->addSelect('email.email')
            ->addJoin('Email AS email', 'INNER')
            ->addWhere('email.is_primary', '=', TRUE)
            ->addWhere('id', '=', $contact_id_cc)
            ->execute()->single();

          \Civi::log()->debug('   ... got mail: ' . $contact['email.email']);
          $cc_mails[] = $contact['email.email'];
        }
        catch (\Exception $e) {
          // Do nothing but log it...
          \Civi::log()->error('$SendMailWithRolesOnCase: no contact with primary mail found for contact_id: ' . $contact_id_cc . ' - ' . $e->getMessage());
        }

        $cc_contacts_output[] = $contact_id_cc;
      }
      \Civi::log()->debug('CC Mails: ' . print_r($cc_mails, TRUE));
      if (!empty($cc_mails)) {
        $cc = implode(', ', $cc_mails);
        \Civi::log()->info('to contact cc mails: ' . $cc);
      }
    }

    $output->setParameter('cc_contact_ids', $cc_contacts_output);

    $mailer = new \Civi\ActionProvider\Utils\SendEmail();

    $mailer->setCaseId($case_id);

    if ($parameters->doesParameterExists('from_name')) {
      $mailer->setFromName($parameters->getParameter('from_name'));
    }
    elseif ($this->configuration->doesParameterExists('from_name')) {
      $mailer->setFromName($this->configuration->getParameter('from_name'));
    }
    if ($parameters->doesParameterExists('from_email')) {
      $mailer->setFromEmail($parameters->getParameter('from_email'));
    }
    elseif ($this->configuration->doesParameterExists('from_email')) {
      $mailer->setFromEmail($this->configuration->getParameter('from_email'));
    }

    $bcc = $this->configuration->getParameter('bcc');
    if ($parameters->doesParameterExists('bcc') && !empty($parameters->getParameter('bcc'))) {
      $bcc = $parameters->getParameter('bcc');
    }

    $subject = $parameters->getParameter('subject');

    $body_html = $parameters->getParameter('body_html');
    $body_text = '';
    if ($parameters->doesParameterExists('body_text')) {
      $body_text = $parameters->getParameter('body_text');
    }

    if ($parameters->doesParameterExists('attachments') && !empty($parameters->getParameter('attachments')[0])) {
      \Civi::log()->debug('$SendMailWithRolesOnCase - attachments: ' . print_r($parameters->getParameter('attachments'), TRUE));

      $config = \CRM_Core_Config::singleton();
      foreach ($parameters->getParameter('attachments') as $path) {
        try {
          $filename = FALSE;
          $mime_type = FALSE;
          if (is_numeric($path) && !file_exists($path)) {
            $file = \Civi\Api4\File::get(FALSE)
              ->addWhere('id', '=', $path)
              ->execute()->single();
            $filename = \CRM_Utils_File::cleanFileName($file['uri']);
            $path = $config->customFileUploadDir . DIRECTORY_SEPARATOR . $file['uri'];
            $path = str_replace('//', '/', $path);
            $mime_type = $file['mime_type'];
          }
          if (file_exists($path)) {
            if (!$mime_type && preg_match('/\.pdf$/i', $path)) {
              // if the file is not created yet, fix the mime type for most common case
              $mime_type = 'application/pdf';
            }

            if (!$mime_type) {
              $mime_type = mime_content_type($path);
            }

            if (!$filename) {
              $filename = basename($path);
              $filename = \CRM_Utils_File::cleanFileName($filename);
            }

            \Civi::log()
              ->info('$SendMailWithRolesOnCase - attachment: [' . $path . "] - mime_type: [$mime_type] - filename: [$filename]");

            $mailer->addAttachment($path, $filename, $mime_type);
          }
          else {
            \Civi::log()
              ->warning('$SendMailWithRolesOnCase - attachment: ' . $path . ' does not exist');
          }
        }
        catch (\Exception $e) {
          // Do nothing but log it...
          \Civi::log()
            ->error('$SendMailWithRolesOnCase - attachment: ' . $e->getMessage());
          continue;
        }
      }
    }
    if (self::DEBUG) {
      \Civi::log()
        ->debug('$SendMailWithRolesOnCase - case_id: ' . $case_id . ' - to contact_id: ' . $contact_id . ' - cc: ' . $cc);
    }
    else {
      $mailer->send([$contact_id], $subject, $body_text, $body_html, [], $cc, $bcc);
    }
  }

  /**
   * Returns the specification of the output parameters for this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE),
      new Specification('cc_contact_ids', 'Integer', E::ts('CC Contact IDs'), FALSE, NULL, 'Contact', NULL, TRUE),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('from_name', 'String', E::ts('From name'), FALSE),
      new Specification('from_email', 'String', E::ts('From email'), FALSE),
      new Specification('bcc', 'String', E::ts('BCC'), FALSE),
    ]);
  }

  /**
   * Returns the specification of the parameters for the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $attachments = new Specification('attachments', 'String', E::ts('Attachment(s)'), FALSE, NULL, NULL, NULL, TRUE);
    $attachments->setDescription(E::ts('Give either the path to the file or the File ID in the CiviCRM database.'));
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), TRUE),
      new Specification('relationship_main', 'Integer', E::ts('Mail Relationship main'), TRUE),
      // we have to use a string here because formfieldlibrary return coma-separated values (single)
      // and for form-processor we have to use multi...
      new Specification('relationship_cc', 'String', E::ts('Mail Relationship cc'), FALSE, NULL, NULL, NULL, TRUE),
      new Specification('from_name', 'String', E::ts('From name'), FALSE),
      new Specification('from_email', 'String', E::ts('From email'), FALSE),
      new Specification('bcc', 'String', E::ts('BCC'), FALSE),
      new Specification('subject', 'String', E::ts('Subject'), TRUE),
      new Specification('body_html', 'String', E::ts('HTML Body'), TRUE),
      new Specification('body_text', 'String', E::ts('Plain text Body'), FALSE),
      $attachments,
    ]);
  }

}
