<?php
namespace Civi\ActionProvider\Action\SMS;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_DAO;
use CRM_Core_Session;
use CRM_Mailing_Info;

class SMSSend extends AbstractAction {

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
      new Specification('msg_name', 'String', E::ts('Message Subject'), true, null, null, null, False),
      new Specification('msg_text', 'Text', E::ts('Message Text'), true, null, null, null, FALSE),
      new Specification('group_id', 'Integer', E::ts('Group ID'), true, null, 'Group', null, FALSE),
      new Specification('scheduled_date', 'Timestamp', E::ts('Scheduled date'), false, null, null, null, False),
      new Specification('sms_provider_id', 'String', E::ts('SMS Provider ID'), true, null, null, null, False),
      new Specification('created_id', 'String', E::ts('User Creator ID'), false, null, null, null, False),
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
      new Specification('id', 'Integer', E::ts('Bulk SMSMail record ID')),
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
    $apiParams['name'] = $parameters->getParameter('msg_name');
    $apiParams['subject'] = $parameters->getParameter('msg_name');
    $apiParams['body_text'] = $parameters->getParameter('msg_text');
    $apiParams['sms_provider_id'] = $parameters->getParameter('sms_provider_id');
    $apiParams['from_name'] = CRM_Core_DAO::getFieldValue('CRM_SMS_DAO_Provider', $apiParams['sms_provider_id'], 'username');
    $apiParams['footer_id'] = NULL;
    $apiParams['reply_id'] = NULL;
    $apiParams['unsubscribe_id'] = NULL;
    $apiParams['resubscribe_id'] = NULL;
    $apiParams['optout_id'] = NULL;
    $apiParams['mailing_type'] = NULL;
    $apiParams['dedupe_email'] = 0;

    if ($parameters->doesParameterExists('created_id') && $parameters->getParameter('created_id')) {
      $apiParams['created_id'] = $parameters->getParameter('created_id');
    }
    // Create a simple SMS Mailing.
    $smsMailing = civicrm_api3('Mailing', 'Create', $apiParams);

    $session = CRM_Core_Session::singleton();
    // set the scheduled_id
    $mailingSendParams['scheduled_id'] = $session->get('userID');
    // set approval details if workflow is not enabled
    if (!CRM_Mailing_Info::workflowEnabled()) {
      $mailingSendParams['approver_id'] = $session->get('userID');
      $mailingSendParams['approval_date'] = date('YmdHis');
      $mailingSendParams['approval_status_id'] = 1;
    }

    $groupIds = $parameters->getParameter('group_id');

    if (!is_array($groupIds)) {
      $groupIds = array($groupIds);
    }
    if (!count($groupIds)) {
      throw new ExecutionException('No receivers selected (group_id is empty)');
    }

    foreach($groupIds as $groupId) {
      $apiGroupParams['group_type'] = 'Include';
      $apiGroupParams['entity_table'] = 'civicrm_group';
      $apiGroupParams['entity_id'] = $groupId;
      $apiGroupParams['mailing_id'] = $smsMailing['id'];
      civicrm_api3('MailingGroup', 'create', $apiGroupParams);
    }
    if ($parameters->doesParameterExists('scheduled_date') && $parameters->getParameter('scheduled_date')) {
      $scheduled_date = $parameters->getParameter('scheduled_date');
    }

    // Now send the mailing
    if(!empty($scheduled_date)) {
      $now = new \DateTime($scheduled_date, new \DateTimeZone('UTC'));
    }
    else {
      $now = new \DateTime();
      $now->setTimezone(new \DateTimeZone('UTC'));
    }

    $mailingSendParams['id'] = $smsMailing['id'];
    $mailingSendParams['scheduled_date'] = $now->format('Ymd His');

    civicrm_api3('Mailing', 'submit', $mailingSendParams);
    $output->setParameter('id', $smsMailing['id']);
  }

}
