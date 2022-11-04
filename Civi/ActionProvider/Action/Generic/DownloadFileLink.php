<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class DownloadFileLink extends AbstractAction {

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

    $sql = "SELECT civicrm_file.uri as uri, civicrm_entity_file.entity_id as entity_id
            FROM civicrm_file
            LEFT JOIN civicrm_entity_file  ON ( civicrm_entity_file.file_id = civicrm_file.id )
            WHERE civicrm_file.id = %1";
    $dao = \CRM_Core_DAO::executeQuery($sql, [1=>[$file_id, 'Integer']]);
    if ($dao->fetch()) {
      $fileHash = \CRM_Core_BAO_File::generateFileHash($dao->entity_id, $file_id);
      $filename = \CRM_Utils_File::cleanFileName($dao->uri);
      $url = \CRM_Utils_System::url('civicrm/file', "reset=1&id={$file_id}&eid={$dao->entity_id}&fcs={$fileHash}", TRUE);
      $output->setParameter('filename', $filename);
      $output->setParameter('url', $url);
      $output->setParameter('link', '<a href="'.$url.'">'.$filename.'</a>');
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
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('url', 'String', E::ts('URL')),
      new Specification('filename', 'String', E::ts('Filename')),
      new Specification('link', 'String', E::ts('Link')),
    ]);
  }


}
