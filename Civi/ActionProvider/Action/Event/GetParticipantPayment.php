<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use CRM_ActionProvider_ExtensionUtil as E;

class GetParticipantPayment extends AbstractAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'ParticipantPayment';
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
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), FALSE, null, null, null, FALSE),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), FALSE, null, null, null, FALSE),
    ));

    return $specs;
  }


  /**
   * @throws \CRM_Core_Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
     $apiParams = [];
     if($parameters->doesParameterExists('participant_id')){
       $apiParams['participant_id'] = $parameters->getParameter('participant_id');
     };
    if($parameters->doesParameterExists('contribution_id')){
      $apiParams['participant_id'] = $parameters->getParameter('contribution_id');
    }
    if(empty($apiParams)){
      throw new \CRM_Extension_Exception('GetParticipantPayment: need one of participant id or contribution id');
    }
    $participantPayment = civicrm_api3('ParticipantPayment', 'getsingle', $apiParams);
    $output->setParameter('id',$participantPayment['id']);
    $output->setParameter('participant_id',$participantPayment['participant_id']);
    $output->setParameter('contribution_id',$participantPayment['contribution_id']);
  }

  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('participant_id', 'Integer', E::ts('Participant ID')),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID')),
      new Specification('id', 'Integer', E::ts('Participant Payment ID')),
    ));
  }

}
