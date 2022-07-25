<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>, J. Margraf <margraf@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class ContactHasActivity extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    try {
        $activity_count = civicrm_api3('Activity', 'getcount', array('activity_type_id' => $this->configuration->getParameter('activity_type_id'), 'contact_id' => $parameterBag->getParameter('contact_id')));
    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing
    }

    $inverse = $this->configuration->getParameter('inverse');
    if ($activity_count > 0) {
      if($inverse){
        return false;
      }
      return true;
    }
    if($inverse){
        return true;
      }
    return false;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $optionGroupId = civicrm_api3('OptionGroup', 'getSingle', array('name' => 'activity_type'));
    $activityTypesApi = civicrm_api3('OptionValue', 'get', array('option_group_id' => $optionGroupId['id'], 'options' => array('limit' => 0)));
    $activityTypes = array();
    foreach($activityTypesApi['values'] as $activityType) {
      $activityTypes[$activityType['value']] = $activityType['label'];
    }
    return new SpecificationBag(array(
      new Specification('inverse', 'String', E::ts('Condition'), false, null, null, $this->getInvertOptions()),
      new Specification('activity_type_id', 'Integer', E::ts('Activity Type'), true, null, null, $activityTypes, FALSE),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual condition.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID')),
    ));
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Contact has activity');
  }


  protected function getInvertOptions() {
    return [
            0  => E::ts("contact has activity"),
            1  => E::ts("contact has no activity"),
        ];
  }

}
