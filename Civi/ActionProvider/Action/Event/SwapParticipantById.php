<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractGetSingleAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use Civi\Api4\Participant;
use CRM_ActionProvider_ExtensionUtil as E;

class SwapParticipantById extends AbstractGetSingleAction {


  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Participant';
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
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
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true, null, null, null, FALSE),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true, null, null, null, FALSE),
    ));

    return $specs;
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Get the contact and the event.
    $participant_id = $parameters->getParameter('participant_id');
    $contact_id = $parameters->getParameter('contact_id');
    if (!$participant_id || !$contact_id ) {
      return;
    }

    try {
      $new_participant = Participant::update()
        ->addValue('contact_id', $contact_id)
        ->addWhere('id', '=', $participant_id)
        ->execute()
        ->first();
      $output->setParameter('participant_id', $participant_id);
      $output->setParameter('contact_id', $contact_id);
      $output->setParameter('updated_participant', $new_participant);
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('participant_id');
  }

}