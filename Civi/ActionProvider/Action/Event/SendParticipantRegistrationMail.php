<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class SendParticipantRegistrationMail extends AbstractAction {

  private static $profiles;

  public function __construct() {
    if (!self::$profiles) {
      $profiles = civicrm_api3('UFGroup', 'get', ['is_active' => 1, 'options' => ['limit' => 0]]);
      foreach($profiles['values'] as $profile) {
        self::$profiles[$profile['name']] = $profile['title'];
      }
    }
  }

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
    $participantId = $parameters->getParameter('participant_id');
    $participant = civicrm_api3('Participant','getsingle',['id' => $participantId]);
    $event =  civicrm_api3('Event','getsingle',['id' => $participant['event_id']]);
    $event['is_email_confirm'] = true; // Hack to enable sending of e-mail
    $event['confirm_from_name'] = $this->configuration->getParameter('from_name');
    $event['confirm_from_email'] = $this->configuration->getParameter('from_email');
    $custom_pre_id = null;
    if ($this->configuration->getParameter('profile')) {
      try {
        $profile = civicrm_api3('UFGroup', 'getsingle', ['name' => $this->configuration->getParameter('profile')]);
        $custom_pre_id = $profile['id'];
      } catch (\CiviCRM_API3_Exception $ex) {
        // Do nothing.
      }
    }
    $location = array();
    if (\CRM_Utils_Array::value('is_show_location',$event) == 1) {
      $locationParams = array(
        'entity_id' => $event['id'],
        'entity_table' => 'civicrm_event',
      );
      $location = \CRM_Core_BAO_Location::getValues($locationParams, TRUE);
      \CRM_Core_BAO_Address::fixAddress($location['address'][1]);
    }
    $lineItem[] = \CRM_Price_BAO_LineItem::getLineItems($participantId);

    $values = array(
      'params' => array($participantId => $participant),
      'event' => $event,
      'location' => $location,
      'custom_pre_id' => $custom_pre_id,
      'custom_post_id' => null,
      'payer' => null,
      'customGroup' => null,
      'lineItem' => $lineItem,
    );
    \CRM_Event_BAO_Event::sendMail($participant['contact_id'],$values,$participantId);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('from_name', 'String', E::ts('From Name'), true),
      new Specification('from_email', 'String', E::ts('From Email'), true),
      new Specification('profile', 'String', E::ts('Profile'), false, null, null, self::$profiles),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true),
    ]);
  }


}
