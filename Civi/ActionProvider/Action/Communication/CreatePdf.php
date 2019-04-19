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

  public function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $domain     = \CRM_Core_BAO_Domain::getDomain();
    $message = $parameters->getParameter('message');
    $contactId = $parameters->getParameter('contact_id');
    $filename = $this->configuration->getParameter('filename');
    $filename .= '_' . $contactId . '.pdf';

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

    $contents = \CRM_Utils_PDF_Utils::html2pdf($message, $filename, TRUE);

    $fullFilePath = FileWriter::writeFile($contents, $filename);
    $mimeType = mime_content_type($fullFilePath);

    $activityParams = array(
      'activity_type_id' => \CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Print PDF Letter'),
      'activity_date_time' => date('YmdHis'),
      'details' => $message,
      'target_contact_id' => $contactId,
    );
    $result = civicrm_api3('Activity', 'create', $activityParams);
    civicrm_api3('Attachment', 'create', array(
      'entity_table' => 'civicrm_activity',
      'entity_id' => $result['id'],
      'name' => $filename,
      'mime_type' => $mimeType,
      'content' => $contents,
    ));

    $downloadUrl = \CRM_Utils_System::url('civicrm/actionprovider/downloadfile', array('filename' => $filename));
    \CRM_Core_Session::setStatus(E::ts('Created document for %1 <a href="%2">Download document<a/>', array(1=>$contact['display_name'], 2=>$downloadUrl)));
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


}