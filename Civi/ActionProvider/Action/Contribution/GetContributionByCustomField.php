<?php

namespace Civi\ActionProvider\Action\Contribution;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\AbstractGetSingleAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use Civi\ActionProvider\Utils\Fields;
use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use CRM_ActionProvider_ExtensionUtil as E;

class GetContributionByCustomField extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Contribution';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return 0;
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities(['Contribution']);
    foreach($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
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
    try {
      $apiParams = array();
      foreach($this->getParameterSpecification() as $specGroup) {
        foreach($specGroup->getSpecificationBag() as $spec) {
          if ($parameters->doesParameterExists($spec->getName())) {
            $apiParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
          }
        }
      }
      if (!count($apiParams)) {
        throw new InvalidParameterException(E::ts("No parameter given"));
      }
      $entity = civicrm_api3($this->getApiEntity(), 'getsingle', $apiParams);
      if ($entity) {
        $this->setOutputFromEntity($entity, $output);
      }
      $participantId = civicrm_api3('ParticipantPayment', 'getvalue', ['contribution_id' => $entity['id'], 'return' => 'participant_id']);
      $output->setParameter('participant_id', $participantId);
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $specs = parent::getOutputSpecification();
    $specs->addSpecification(new Specification('participant_id', 'Integer', E::ts('Participant ID')));
    return $specs;
  }


}
