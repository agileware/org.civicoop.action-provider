<?php
/**
 * @author Micah <micah@circle-interactive.co.uk>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractGetSingleAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class GetCaseDataByCustomField extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Case';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return 0; // We override doAction to use custom field search
  }

  /**
   * @return array
   */
  protected function getSkippedFields() {
    return ['contacts', 'activities', 'client_id'];
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities(['Case']);
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
    }
    catch (\Exception $e) {
      // Do nothing
    }
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    parent::setOutputFromEntity($entity, $output);
    $output->setParameter('contact_id', reset($entity['contact_id']));
    $output->setParameter('case_type_id', $entity['case_type_id']);
  }

}