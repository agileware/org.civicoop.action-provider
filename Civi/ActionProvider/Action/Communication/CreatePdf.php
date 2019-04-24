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

use Civi\ActionProvider\Utils\FileWriter;
use CRM_ActionProvider_ExtensionUtil as E;

class CreatePdf extends AbstractAction {

  /**
   * @var \ZipArchive
   */
  protected $zip;

  public function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $domain     = \CRM_Core_BAO_Domain::getDomain();
    $message = $parameters->getParameter('message');
    $contactId = $parameters->getParameter('contact_id');
    $filename = $this->configuration->getParameter('filename');
    $fileNameWithoutContactId = $filename . '.pdf';
    $filenameWithContactId = $filename . '_' . $contactId . '.pdf';
    $subdir = $this->createSubDir($this->currentBatch);

    $contact = civicrm_api3('Contact', 'getsingle', array('id' => $contactId));

    $tokens = \CRM_Utils_Token::getTokens($message);

    \CRM_Utils_Hook::tokenValues($contact, $contactId, NULL, $tokens);
    // call token hook
    $hookTokens = array();
    \CRM_Utils_Hook::tokens($hookTokens);
    $categories = array_keys($hookTokens);

    $message = \CRM_Utils_Token::replaceDomainTokens($message, $domain, TRUE, $tokens, TRUE);
    $message = \CRM_Utils_Token::replaceHookTokens($message, $contact, $categories, TRUE);
    \CRM_Utils_Token::replaceGreetingTokens($message, $contact, $contactId);
    $message = \CRM_Utils_Token::replaceContactTokens($message, $contact, FALSE, $tokens, FALSE, TRUE);
    $message = \CRM_Utils_Token::replaceComponentTokens($message, $contact, $tokens, TRUE);

    if (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY) {
      $smarty = \CRM_Core_Smarty::singleton();
      $message = $smarty->fetch("string:{$message}");
    }

    $contents = \CRM_Utils_PDF_Utils::html2pdf($message, $filenameWithContactId, TRUE);
    if ($this->zip) {
      $this->zip->addFromString($filenameWithContactId, $contents);
    }

    $activityParams = array(
      'activity_type_id' => \CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Print PDF Letter'),
      'activity_date_time' => date('YmdHis'),
      'details' => $message,
      'target_contact_id' => $contactId,
    );
    $result = civicrm_api3('Activity', 'create', $activityParams);
    $attachment = civicrm_api3('Attachment', 'create', array(
      'entity_table' => 'civicrm_activity',
      'entity_id' => $result['id'],
      'name' => $fileNameWithoutContactId,
      'mime_type' => 'application/pdf',
      'content' => $contents,
    ));

    $file = reset($attachment['values']);

    $output->setParameter('filename', $file['name']);
    $output->setParameter('url', $file['url']);
    $output->setParameter('path', $file['path']);
  }

  /**
   * This function initialize a batch.
   *
   * @param $batchName
   */
  public function initializeBatch($batchName) {
    // Child classes could override this function
    // E.g. create a directory
    $this->createSubDir($batchName);

    $subdir = $this->createSubDir();
    $outputName = \CRM_Core_Config::singleton()->templateCompileDir . $subdir.'/'.$batchName.'.zip';
    $this->zip = new \ZipArchive();
    if ($this->zip->open($outputName, \ZipArchive::CREATE) !== TRUE) {
      $this->zip = null;
    }

    $this->currentBatch = $batchName;
  }

  /**
   * This function finishes a batch and is called when a batch with actions is finished.
   *
   * @param $batchName
   * @param bool
   *   Whether this was the last batch.
   */
  public function finishBatch($batchName, $isLastBatch=false) {
    // Child classes could override this function
    // E.g. merge files in a directorys
    $subdir = $this->createSubDir();
    $downloadName = $this->configuration->getParameter('filename').'.zip';
    if ($this->zip) {
      $this->zip->close();

      if ($isLastBatch) {
        $downloadUrl = \CRM_Utils_System::url('civicrm/actionprovider/downloadfile', [
          'filename' => $batchName . '.zip',
          'subdir' => $subdir,
          'downloadname' => $downloadName
        ]);
        \CRM_Core_Session::setStatus(E::ts('<a href="%1">Download document(s)<a/>', [1 => $downloadUrl]), E::ts('Created PDF'), 'success');
      }
    }
  }

  protected function createSubDir() {
    $subDir = 'action_provider';
    $basePath = \CRM_Core_Config::singleton()->templateCompileDir . $subDir;
    \CRM_Utils_File::createDir($basePath);
    \CRM_Utils_File::restrictAccess($basePath.'/');
    $subDir .= '/createpdf';
    $basePath = \CRM_Core_Config::singleton()->templateCompileDir . $subDir;
    \CRM_Utils_File::createDir($basePath);
    \CRM_Utils_File::restrictAccess($basePath.'/');
    return $subDir;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('message', 'String', E::ts('Message'), true),
    ));
  }

  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('filename', 'String', E::ts('Filename'), true, E::ts('document')),
    ));
  }

  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('filename', 'String', E::ts('Filename')),
      new Specification('url', 'String', E::ts('Download Url')),
      new Specification('path', 'String', E::ts('Path in filesystem')),
    ));
  }


}