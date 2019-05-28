<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\FileSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UploadCustomFileField extends AbstractAction {
  
  /**
   * Run the action
   * 
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back 
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $event_id = $parameters->getParameter('event_id');
    $customFieldId = $this->configuration->getParameter('custom_field');
    $file = $parameters->getParameter('file');
    $deleteCurrentFile = false;
    $uploadNewOne = true;
    $updateCustomField = false;
    if (empty($file)) {
      $deleteCurrentFile = true;
      $uploadNewOne = false;
    } elseif (!isset($file['id'])) {
      $deleteCurrentFile = true;
    } elseif (isset($file['id'])) {
      $uploadNewOne = false;
    }
    try {
      $currentFile = civicrm_api3('Event', 'getvalue', [
        'id' => $event_id,
        'return' => 'custom_' . $customFieldId
      ]);
      if (isset($file['id']) && $currentFile && $file['id'] != $currentFile) {
        $deleteCurrentFile = true;
        $updateCustomField = $file['id'];
      }
      if ($currentFile && $deleteCurrentFile) {
        civicrm_api3('Attachment', 'delete', array('id' => $currentFile));
      }
    } catch (\Exception $e) {
      // Do nothing
    }


    if ($uploadNewOne) {
      $attachmentParams = [
        'entity_table' => 'civicrm_event',
        'entity_id' => $event_id,
        'name' => $file['name'],
        'content' => base64_decode($file['content']),
        'mime_type' => $file['mime_type'],
        'check_permissions' => FALSE,
      ];
      $result = civicrm_api3('Attachment', 'create', $attachmentParams);
      $updateCustomField = $result['id'];
    }
    if ($updateCustomField) {
      civicrm_api3('Event', 'create', [
        'id' => $event_id,
        'custom_' . $customFieldId => $updateCustomField,
      ]);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $fileFields = array();
    $customGroups = civicrm_api3('CustomGroup', 'get', array('is_active' => 1, 'is_multiple' => 0, 'extends' => 'Event', 'options' => array('limit' => 0)));
    foreach($customGroups['values'] as $customGroup) {
      if (!in_array($customGroup['extends'], [
        'Event'
      ])) {
        continue;
      }

      $customFields = civicrm_api3('CustomField', 'get', [
        'custom_group_id' => $customGroup['id'],
        'is_active' => 1,
        'data_type' => 'File',
        'options' => ['limit' => 0]
      ]);
      foreach ($customFields['values'] as $customField) {
        $fileFields[$customField['id']] = $customGroup['title']. ' :: '.$customField['label'];
      }
    }

    return new SpecificationBag([
      new Specification('custom_field', 'Integer', E::ts('File field'), true, null, null, $fileFields, false),
    ]);
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('event_id', 'Integer', E::ts('Event ID'), true));
    $specs->addSpecification(new FileSpecification('file', E::ts('File'), false));
    return $specs;
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