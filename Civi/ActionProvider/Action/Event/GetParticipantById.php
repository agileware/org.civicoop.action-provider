<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractGetSingleAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use Civi\API\Exception\UnauthorizedException;
use CRM_ActionProvider_ExtensionUtil as E;

class GetParticipantById extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Participant';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('participant_id');
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
    if (!$participant_id) {
      return;
    }

    try {
      $participant = \Civi\Api4\Participant::get(TRUE)
        ->addSelect('*', 'custom.*')
        ->addWhere('id', '=', $participant_id)
        ->execute()
        ->first();
      /** @var Specification $spec */
      foreach($this->getOutputSpecification() as $spec) {
        if (!empty($spec->getApi4FieldName()) && isset($participant[$spec->getApi4FieldName()])) {
          $participant[$spec->getApiFieldName()] = $participant[$spec->getApi4FieldName()];
          unset($participant[$spec->getApi4FieldName()]);
        }
      }
      $this->setOutputFromEntity($participant, $output);
    }
    catch (UnauthorizedException|\CRM_Core_Exception $e) {

    }
  }


}
