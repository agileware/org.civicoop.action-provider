<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class SendEmailToParticipants extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $participantParams['event_id'] = $parameters->getParameter('event_id');
    $participantParams['options']['limit'] = 0;
    if ($parameters->doesParameterExists('with_status')) {
      $status = $parameters->getParameter('with_status');
      if (is_array($status)) {
        $participantParams['status_id']['IN'] = $status;
      } else {
        $participantParams['status_id'] = $status;
      }
    }

    $participants = civicrm_api3('Participant', 'get', $participantParams);
    $contactIds = array();
    foreach($participants['values'] as $participant) {
      $contactIds[] = $participant['contact_id'];
    }
    $senderContact = civicrm_api3('Contact', 'getsingle', array('id' => $parameters->getParameter('sender_contact_id')));

    //$email = new \Civi\ActionProvider\Utils\SendEmail($senderContact['email'], $senderContact['display_name']);
    $email = new \Civi\ActionProvider\Utils\SendEmail();
    $email->send($contactIds, $parameters->getParameter('subject'), $parameters->getParameter('message'), $parameters->getParameter('message'));
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
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('event_id', 'Integer', E::ts('Event'), true),
      new Specification('sender_contact_id', 'Integer', E::ts('Sender contact ID'), true),
      new Specification('subject', 'String', E::ts('subject'), true),
      new Specification('message', 'String', E::ts('Message'), true),
      new Specification('with_status', 'Integer', E::ts('With participant status'), false, null, 'ParticipantStatusType', null, true)
    ));
  }

}