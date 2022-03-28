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

use CRM_ActionProvider_ExtensionUtil as E;

class MessageTemplateByName extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $messageTemplateBao = new \CRM_Core_DAO_MessageTemplate();
    $messageTemplateBao->is_active = 1;
    $messageTemplateBao->find();
    $messageTemplateBao->msg_title = $this->configuration->getParameter('name');
    if ($messageTemplateBao->find(TRUE)) {
      $output->setParameter('subject', $messageTemplateBao->msg_subject);
      $output->setParameter('body_html', $messageTemplateBao->msg_html);
      $output->setParameter('body_text', $messageTemplateBao->msg_text);
      if ($messageTemplateBao->pdf_format_id) {
        $output->setParameter('page_format_id', $messageTemplateBao->pdf_format_id);
      }
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $messageTemplates = array();
    $messageTemplateBao = new \CRM_Core_DAO_MessageTemplate();
    $messageTemplateBao->is_active = 1;
    $messageTemplateBao->find();
    while($messageTemplateBao->fetch()) {
      if (empty($messageTemplateBao->workflow_id)) {
        $messageTemplates[$messageTemplateBao->msg_title] = $messageTemplateBao->msg_title;
      }
    }
    return new SpecificationBag(array(
      new Specification('name', 'String', E::ts('Name'), true, null, null, $messageTemplates),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('subject', 'String', E::ts('Subject')),
      new Specification('body_html', 'String', E::ts('HTML Body')),
      new Specification('body_text', 'String', E::ts('Plain text Body')),
      new Specification('page_format_id', 'Integer', E::ts('Print Page (PDF) Format')),
    ));
  }

}
