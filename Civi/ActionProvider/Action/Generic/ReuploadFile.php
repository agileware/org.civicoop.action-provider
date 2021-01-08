<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\FileSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class ReuploadFile extends AbstractAction {

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText() {
    return E::ts('This action makes it possible to copy an existing file and reuse it for something else.');
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
    $file_id = $parameters->getParameter('file_id');

    $sql = "SELECT civicrm_file.uri as uri, civicrm_entity_file.entity_id as entity_id, civicrm_file.mime_type as mime_type
            FROM civicrm_file
            LEFT JOIN civicrm_entity_file  ON ( civicrm_entity_file.file_id = civicrm_file.id )
            WHERE civicrm_file.id = %1";
    $dao = \CRM_Core_DAO::executeQuery($sql, [1=>[$file_id, 'Integer']]);
    if ($dao->fetch()) {
      $filename = \CRM_Utils_File::cleanFileName($dao->uri);
      $fileHash = \CRM_Core_BAO_File::generateFileHash($dao->entity_id, $file_id);
      $url = \CRM_Utils_System::url('civicrm/file', "reset=1&id={$file_id}&eid={$dao->entity_id}&fcs={$fileHash}", TRUE);
      $file['mime_type'] = $dao->mime_type;
      $file['name'] = $filename;
      $file['url'] = $url;
      $output->setParameter('file', $file);
    }

  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('file_id', 'Integer', E::ts('File ID'), true),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new FileSpecification('file', E::ts('File')),
    ]);
  }


}
