<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\BulkMail;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\FileSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class AddAttachment extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $mailing_id = $parameters->getParameter('mailing_id');
    $file = $parameters->getParameter('file');
    if (empty($file)) {
      return;
    }

    $attachmentParams = [
      'entity_table' => 'civicrm_mailing',
      'entity_id' => $mailing_id,
      'name' => $file['name'],
      'content' => base64_decode($file['content']),
      'mime_type' => $file['mime_type'],
      'check_permissions' => FALSE,
    ];
    $result = civicrm_api3('Attachment', 'create', $attachmentParams);
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
      new Specification('mailing_id', 'Integer', E::ts('Mailing ID'), true),
      new FileSpecification('file', E::ts('File'), false),
    ]);
  }


}
