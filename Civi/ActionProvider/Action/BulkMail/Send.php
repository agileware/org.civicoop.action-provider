<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\BulkMail;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class Send extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      /**
       * The parameters given to the Specification object are:
       * @param string $name
       * @param string $dataType
       * @param string $title
       * @param bool $required
       * @param mixed $defaultValue
       * @param string|null $fkEntity
       * @param array $options
       * @param bool $multiple
       */
      new Specification('name', 'String', E::ts('Name'), true, null, null, null, False),
      new Specification('subject', 'String', E::ts('Subject'), true, null, null, null, False),
      new Specification('body_html', 'Text', E::ts('HTML Body'), true, null, null, null, FALSE),
      new Specification('template_options', 'Text', E::ts('Template Options'), true, null, null, null, FALSE),
      new Specification('group_id', 'Integer', E::ts('Select group'), true, null, 'Group', null, TRUE),
      new Specification('sender_contact_id', 'Integer', E::ts('Sender Contact ID'), true),
      new Specification('from_name', 'String', E::ts('From name'), false, null, null, null, False),
      new Specification('from_email', 'String', E::ts('From E-mail'), false, null, null, null, False),
      new Specification('replyto_email', 'String', E::ts('Reply to'), false, null, null, null, False),
    ));
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
    return new SpecificationBag(array(
      new Specification('id', 'Integer', E::ts('Bulk mail record ID')),
    ));
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $sender_contact = civicrm_api3('Contact', 'getsingle', array('id' => $parameters->getParameter('sender_contact_id')));

    $groupIds = $parameters->getParameter('group_id');
    if (!is_array($groupIds)) {
      $groupIds = array($groupIds);
    }
    if (!count($groupIds)) {
      throw new ExecutionException('No receivers selected (group_id is empty)');
    }

    $apiParams['name'] = $parameters->getParameter('name');
    $apiParams['subject'] = $parameters->getParameter('subject');
    $apiParams['body_html'] = $parameters->getParameter('body_html');
    $apiParams['created_id'] = $parameters->getParameter('sender_contact_id');
    $apiParams['header_id'] = 'null';
    $apiParams['footer_id'] = 'null';
    if (isset($sender_contact['email'])) {
      $apiParams['from_name']  = $sender_contact['display_name'];
      $apiParams['from_email']  = $sender_contact['email'];
      $apiParams['replyto_email'] = $sender_contact['email'];
    }
    if ($parameters->doesParameterExists('from_name') && $parameters->getParameter('from_name')) {
      $apiParams['from_name'] = $parameters->getParameter('from_name');
    }
    if ($parameters->doesParameterExists('from_name') && $parameters->getParameter('from_email')) {
      $apiParams['from_email'] = $parameters->getParameter('from_email');
      $apiParams['replyto_email'] = $parameters->getParameter('from_email');
    }
    if ($parameters->doesParameterExists('from_name') && $parameters->getParameter('from_name')) {
      $apiParams['replyto_email'] = $parameters->getParameter('replyto_email');
    }
    if ($parameters->doesParameterExists('template_options')) {
      $apiParams['template_options'] = $parameters->getParameter('template_options');
    }
    $mailing = civicrm_api3('Mailing', 'Create', $apiParams);

    foreach($groupIds as $groupId) {
      $apiGroupParams['group_type'] = 'Include';
      $apiGroupParams['entity_table'] = 'civicrm_group';
      $apiGroupParams['entity_id'] = $groupId;
      $apiGroupParams['mailing_id'] = $mailing['id'];
      civicrm_api3('MailingGroup', 'create', $apiGroupParams);
    }

    // Now send the mailing
    $now = new \DateTime();
    $now->setTimezone(new \DateTimeZone('UTC'));
    $mailingSendParams['id'] = $mailing['id'];
    $mailingSendParams['scheduled_date'] = $now->format('Ymd His');
    civicrm_api3('Mailing', 'submit', $mailingSendParams);
    $output->setParameter('id', $mailing['id']);
  }

}
