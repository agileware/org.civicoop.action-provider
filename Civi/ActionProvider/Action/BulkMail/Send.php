<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\BulkMail;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class Send extends AbstractAction {

  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Send Bulk Mail');
  }

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
      new Specification('subject', 'String', E::ts('Subject'), true, null, null, null, False),
      new Specification('body_html', 'Text', E::ts('HTML Body'), true, null, null, null, FALSE),
      new Specification('group_id', 'Integer', E::ts('Select group'), true, null, 'Group', null, FALSE),
      new Specification('sender_contact_id', 'Integer', E::ts('Sender Contact ID'), true)
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
    $apiParams['name'] = $parameters->getParameter('subject');
    $apiParams['subject'] = $parameters->getParameter('subject');
    $apiParams['body_html'] = $parameters->getParameter('body_html');
    $apiParams['created_id'] = $parameters->getParameter('sender_contact_id');
    $mailing = civicrm_api3('Mailing', 'Create', $apiParams);
    $apiGroupParams['group_type'] = 'Include';
    $apiGroupParams['entity_table'] = 'civicrm_group';
    $apiGroupParams['entity_id'] = $parameters->getParameter('group_id');
    $apiGroupParams['mailing_id'] = $mailing['id'];
    civicrm_api3('MailingGroup', 'create', $apiGroupParams);
    // Now send the mailing
    $now = new \DateTime();
   $now->setTimezone(new \DateTimeZone('UTC'));
    $mailingSendParams['id'] = $mailing['id'];
    $mailingSendParams['scheduled_date'] = $now->format('Ymd His');
    civicrm_api3('Mailing', 'submit', $mailingSendParams);
    $output->setParameter('id', $mailing['id']);
  }

  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
      'bulk_mail',
    );
  }

}