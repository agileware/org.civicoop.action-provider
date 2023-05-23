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
use CRM_Core_Config;
use CRM_Core_DAO;
use CRM_Utils_File;

class GetFileInfo extends AbstractAction {

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
    $sql = "SELECT civicrm_file.uri as uri, civicrm_entity_file.entity_id as entity_id, civicrm_entity_file.entity_table as entity_table
          FROM civicrm_file
          LEFT JOIN civicrm_entity_file  ON ( civicrm_entity_file.file_id = civicrm_file.id )
          WHERE civicrm_file.id = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, [1=>[$file_id, 'Integer']]);
    if ($dao->fetch()) {
      $fileUri = CRM_Core_Config::singleton()->customFileUploadDir . $dao->uri;
      $filename = CRM_Utils_File::cleanFileName($dao->uri);
      $output->setParameter('filename', $filename);
      $output->setParameter('uri', $dao->uri);
      $output->setParameter('path', $fileUri);
      $output->setParameter('entity_table', $dao->entity_table);
      $output->setParameter('entity_id', $dao->entity_id);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    return new SpecificationBag([]);
  }


  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
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
  public function getOutputSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('uri', 'String', E::ts('URI')),
      new Specification('filename', 'String', E::ts('Filename')),
      new Specification('path', 'String', E::ts('Path')),
      new Specification('entity_table', 'String', E::ts('Entity Table')),
      new Specification('entity_id', 'Integer', E::ts('Entity ID')),
    ]);
  }


}
