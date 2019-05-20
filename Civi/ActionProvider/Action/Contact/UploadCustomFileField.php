<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
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
    $contact_id = $parameters->getParameter('contact_id');
    $customFieldId = $this->configuration->getParameter('custom_field');
    try {
      $currentFile = civicrm_api3('Contact', 'getvalue', [
        'id' => $contact_id,
        'return' => 'custom_' . $customFieldId
      ]);
      if ($currentFile) {
        civicrm_api3('Attachment', 'delete', array('id' => $currentFile));
      }
    } catch (\Exception $e) {
      // Do nothing
    }
    $customField = civicrm_api3('CustomField', 'getsingle', array('id' => $customFieldId));
    $attachmentParams = array(
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contact_id,
      'name' => $parameters->getParameter('file_name'),
      'content' => base64_decode($parameters->getParameter('file_content')),
      'mime_type' => $parameters->getParameter('file_mime_type'),
      'check_permissions' => false,
    );
    $result = civicrm_api3('Attachment', 'create', $attachmentParams);
    civicrm_api3('Contact', 'create', array(
      'id' => $contact_id,
      'custom_'.$customFieldId => $result['id']
    ));
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $fileFields = array();
    $customGroups = civicrm_api3('CustomGroup', 'get', array('is_active' => 1, 'is_multiple' => 0, 'options' => array('limit' => 0)));
    foreach($customGroups['values'] as $customGroup) {
      if (!in_array($customGroup['extends'], [
        'Individual',
        'Household',
        'Organization',
        'Contact'
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
    $specs->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), true));
    $specs->addSpecification(new Specification('file_name', 'String', E::ts('Filename'), true));
    $specs->addSpecification(new Specification('file_mime_type', 'String', E::ts('File mime type'), true));
    $specs->addSpecification(new Specification('file_content', 'String', E::ts('File content (base64 decoded)'), true));
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