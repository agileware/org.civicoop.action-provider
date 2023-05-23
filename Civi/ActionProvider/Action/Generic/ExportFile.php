<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupByNameSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\Files;
use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_Config;
use CRM_Core_DAO;
use CRM_Core_Session;
use CRM_Utils_File;
use CRM_Utils_System;
use ZipArchive;

class ExportFile extends AbstractAction {

  /**
   * @var \ZipArchive
   */
  protected $zip;

  /**
   * @var String
   */
  protected $downloadUrl;

  /**
   * @var String
   */
  protected $downloadName;

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

    if ($this->zip) {
      $sql = "SELECT civicrm_file.uri as uri, civicrm_entity_file.entity_id as entity_id, civicrm_entity_file.entity_table as entity_table
            FROM civicrm_file
            LEFT JOIN civicrm_entity_file  ON ( civicrm_entity_file.file_id = civicrm_file.id )
            WHERE civicrm_file.id = %1";
      $dao = CRM_Core_DAO::executeQuery($sql, [1=>[$file_id, 'Integer']]);
      if ($dao->fetch()) {
        $fileUri = CRM_Core_Config::singleton()->customFileUploadDir . $dao->uri;
        $prefix = $dao->entity_table .'-' . $dao->entity_id.'-';
        if (!empty($parameters->getParameter('prefix'))) {
          $prefix = $parameters->getParameter('prefix');
        }
        $suffix = '-' . $file_id;
        if (!empty($parameters->getParameter('suffix'))) {
          $suffix = $parameters->getParameter('suffix');
        }
        $filename = $prefix . CRM_Utils_File::cleanFileName($dao->uri);
        if (!empty($parameters->getParameter('filename'))) {
          $filename = $parameters->getParameter('filename');
        }
        $ext = CRM_Utils_File::getExtensionFromPath($fileUri);
        if (strlen($ext)) {
          $ext = '.' . $ext;
          $newExt = $suffix . $ext;
          $filename = str_replace($ext, $newExt, $filename);
        } else {
          $filename .= $suffix;
        }
        $this->zip->addFile($fileUri, $filename);

        $output->setParameter('filename', $this->downloadName);
        $output->setParameter('url', $this->downloadUrl);
        $output->setParameter('link', '<a href="'.$this->downloadUrl.'">'.$this->downloadName.'</a>');
      }
    }
  }

  /**
   * This function initialize a batch.
   *
   * @param $batchName
   */
  public function initializeBatch($batchName) {
    $subdir = Files::createRestrictedDirectory('exportfile');
    $outputName = CRM_Core_Config::singleton()->templateCompileDir . $subdir . '/' . $batchName . '.zip';
    $this->zip = new ZipArchive();
    if ($this->zip->open($outputName, ZipArchive::CREATE) !== TRUE) {
      $this->zip = NULL;
    }
    $this->currentBatch = $batchName;

    $this->downloadName = $this->configuration->getParameter('filename').'.zip';
    $this->downloadUrl = CRM_Utils_System::url('civicrm/actionprovider/downloadfile', [
      'filename' => $batchName.'.zip',
      'subdir' => $subdir,
      'downloadname' => $this->downloadName
    ]);
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
    if ($this->zip) {
      $this->zip->close();
      if ($isLastBatch) {
        CRM_Core_Session::setStatus(E::ts('<a href="%1">Download file(s)<a/>', [1 => $this->downloadUrl]), E::ts('Created ZIP'), 'success');
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
    $filename = new Specification('filename', 'String', E::ts('Filename'), true, E::ts('files'));
    $filename->setDescription(E::ts('Without the extension e.g. .zip'));

    return new SpecificationBag(array(
      $filename,
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('file_id', 'Integer', E::ts('File ID'), true),
      new Specification('prefix', 'String', E::ts('File name prefix'), false),
      new Specification('suffix', 'String', E::ts('File name suffix'), false),
      new Specification('filename', 'String', E::ts('Use this file name'), false),
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
