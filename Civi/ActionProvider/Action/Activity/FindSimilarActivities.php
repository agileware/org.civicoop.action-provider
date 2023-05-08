<?php
/**
 * @author Simon Hermann <simon.hermann@civiservice.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\OptionGroupByNameSpecification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class FindSimilarActivities extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $parameterArray = $parameters->toArray();
    $configurationArray = $this->configuration->toArray();


    $apiParams = array();
    $apiParams['return'] = array('id');
    foreach ($parameterArray as $key => $value) {
      if ($value !== '') {
        $apiParams[$key] = $value;
      }
    }
    foreach ($configurationArray as $key => $value) {
      if (!isset($apiParams[$key]) && $value !== '') {
        $apiParams[$key] = $value;
      }
    }

    try {
      $entity = civicrm_api3('Activity', 'get', $apiParams);
      $output->setParameter('number_similar_activities', sizeof($entity['values']));
      $activityIDs = array();
      foreach ($entity['values'] as $entry) {
        array_push($activityIDs, $entry['id']);
      }
      $output->setParameter('activity_Ids', $activityIDs);
      $output->setParameter('apiCall', $apiParams);
    }
    catch (\Exception $e) {
      throw new ExecutionException(E::ts('Could not find an activity'));
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new OptionGroupByNameSpecification('activity_type_id', 'activity_type', E::ts('Activity Type'), FALSE));
    $specs->addSpecification(new OptionGroupByNameSpecification('status_id', 'activity_status', E::ts('Activity Status'), FALSE));
    return $specs;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('activity_type_id', 'String', E::ts('Activity type ID'), false));
    $specs->addSpecification(new Specification('status_id', 'String', E::ts('Activity status ID'), false));
    $specs->addSpecification(new Specification('subject', 'String', E::ts('Activity subject'), false));
    $specs->addSpecification(new Specification('target_contact_id', 'Integer', E::ts('Target contact ID'), false));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Activity');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }

    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('activity_Ids', 'String', E::ts('IDs of similar activities'), true));
    $specs->addSpecification(new Specification('number_similar_activities', 'Integer', E::ts('Number of similar acitivities')));
    $specs->addSpecification(new Specification('apiCall', 'Integer', E::ts('Call of the APIv3')));

    return $specs;
  }


}