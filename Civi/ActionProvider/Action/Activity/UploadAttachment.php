<?php

namespace Civi\ActionProvider\Action\Activity;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\FileSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UploadAttachment extends AbstractAction {

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
    $activity_id = $parameters->getParameter('activity_id');
    $file = $parameters->getParameter('file');
    $uploadNewOne = true;
    if (empty($file)) {
      $uploadNewOne = false;
    } elseif (isset($file['id'])) {
      $uploadNewOne = false;
    }
    try {
      if (isset($file['id'])) {
        civicrm_api3('Attachment', 'delete', array('id' => $file['id']));
      }
    } catch (\Exception $e) {
      // Do nothing
    }

    $content = '';
    if (isset($file['content'])) {
      $content = base64_decode($file['content']);
    } elseif (isset($file['url'])) {
      $content = file_get_contents($file['url']);
    }
    if (empty($content)) {
      return;
    }

    if ($uploadNewOne) {
      $attachmentParams = [
        'entity_table' => 'civicrm_activity',
        'entity_id' => $activity_id,
        'name' => $file['name'],
        'content' => $content,
        'mime_type' => $file['mime_type'],
        'check_permissions' => FALSE,
      ];
      $result = civicrm_api3('Attachment', 'create', $attachmentParams);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('activity_id', 'Integer', E::ts('Activity ID'), true));
    $specs->addSpecification(new FileSpecification('file', E::ts('File'), false));
    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }


}
