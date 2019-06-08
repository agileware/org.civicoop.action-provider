<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;
use Dompdf\Exception;

class CreateRecurringEvent extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $weekDays = array(
      'sunday' => E::ts('Sunday'),
      'monday' => E::ts('Monday'),
      'tuesday' => E::ts('Tuesday'),
      'wednesday' => E::ts('Wednesday'),
      'thursday' => E::ts('Thursday'),
      'friday' => E::ts('Friday'),
      'saturday' => E::ts('Saturday'),
    );

    $daoOfWeekNo = array(
      'first' => E::ts('First'),
      'second' => E::ts('Second'),
      'third' => E::ts('Third'),
      'fourth' => E::ts('Fourth'),
      'last' => E::ts('Last'),
    );

    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('event_id', 'Integer', E::ts('Event ID'), true, false));
    $specs->addSpecification(new Specification('unit', 'String', E::ts('Frequency Unit'), true, false, null, \CRM_Core_SelectValues::getRecurringFrequencyUnits()));
    $specs->addSpecification(new Specification('interval', 'Integer', E::ts('Frequency Interval'), true, 1));
    $specs->addSpecification(new Specification('start_date', 'Timestamp', E::ts('Start from (default to start of event)'), false, null));
    $specs->addSpecification(new Specification('start_action_offset', 'Integer', E::ts('Ends after'), false, null));
    $specs->addSpecification(new Specification('repeat_absolute_date', 'Date', E::ts('End on'), false, null));
    $specs->addSpecification(new Specification('start_action_condition', 'String', E::ts('Repeats On (day of weeks)'), false, null, null, $weekDays, true));
    $specs->addSpecification(new Specification('limit_to_day_of_month', 'Integer', E::ts('Limit to day of the month'), false, null, null, \CRM_Core_SelectValues::getNumericOptions(1, 31)));
    $specs->addSpecification(new Specification('limit_to_day_of_week', 'String', E::ts('Limit to day of week'), false, null, null, $weekDays));
    $specs->addSpecification(new Specification('limit_to_day_of_week_no', 'String', E::ts('Limit to week number in month'), null, null, null, $daoOfWeekNo));


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
      new Specification('id', 'Integer', E::ts('Event ID')),
    ));
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
   * Run the action
   * 
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back 
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $event_id = $parameters->getParameter('event_id');
    $params['repetition_frequency_unit'] = $parameters->getParameter('unit');
    $params['repetition_frequency_interval'] = $parameters->getParameter('interval');
    if ($params['repetition_frequency_unit'] == 'week' && $parameters->doesParameterExists('start_action_condition')) {
      $params['start_action_condition'] = array();
      foreach($parameters->getParameter('start_action_condition') as $weekDay) {
        $params['start_action_condition'][$weekDay] = 1;
      }
    } elseif ($params['repetition_frequency_unit'] == 'month' && $parameters->doesParameterExists('limit_to_day_of_month')) {
      $params['repeats_by'] = 1;
      $params['limit_to'] = $parameters->getParameter('limit_to_day_of_month');
    } elseif ($params['repetition_frequency_unit'] == 'month' && $parameters->doesParameterExists('limit_to_day_of_week_no') && $parameters->doesParameterExists('limit_to_day_of_week')) {
      $params['repeats_by'] = 2;
      $params['entity_status_1'] = $parameters->getParameter('limit_to_day_of_week_no');
      $params['entity_status_2'] = $parameters->getParameter('limit_to_day_of_week');
    }
    $params['allowRepeatConfigToSubmit'] = 1;
    $params['entity_id'] = $event_id;
    $params['dateColumns'] = array('start_date');
    $params['excludeDateRangeColumns'] = array('start_date', 'end_date');
    $params['entity_table'] = 'civicrm_event';
    $params['used_for'] = 'civicrm_event';
    $config = \CRM_Core_Config::singleton();
    if ($parameters->doesParameterExists('start_date')) {
      $startDate = new \DateTime(\CRM_Utils_Date::customFormat($parameters->getParameter('start_date'), '%Y-%m-%d %H:%i'));
      $params['repetition_start_date'] = \CRM_Utils_Date::customFormat($parameters->getParameter('start_date'), $config->dateformatFull);
      $params['repetition_start_date_time'] = $startDate->format('H:i');
    } else {
      $startDate = civicrm_api3('Event', 'getvalue', array('id' => $event_id, 'return' => 'start_date'));
      $startDate = new \DateTime($startDate);
      $params['repetition_start_date'] = \CRM_Utils_Date::customFormat($startDate->format('Ymd His'), $config->dateformatFull);
      $params['repetition_start_date_time'] = $startDate->format('H:i');
    }
    $params['ends'] = $parameters->getParameter('ends');
    if ($parameters->doesParameterExists('start_action_offset') && $parameters->getParameter('start_action_offset')) {
      $params['ends'] = 1;
      $params['start_action_offset'] = $parameters->getParameter('start_action_offset');
    } elseif ($parameters->doesParameterExists('repeat_absolute_date') && $parameters->getParameter('repeat_absolute_date')) {
      $params['ends'] = 2;
      $params['repeat_absolute_date'] = \CRM_Utils_Date::customFormat($parameters->getParameter('repeat_absolute_date'), $config->dateformatFull);
    } else {
      // Invalid configuration
      return;
    }

    // CRM-16568 - check if parent exist for the event.
    $parentId = \CRM_Core_BAO_RecurringEntity::getParentFor($event_id, 'civicrm_event');
    $params['parent_entity_id'] = !empty($parentId) ? $parentId : $params['entity_id'];

    //Save post params to the schedule reminder table
    $recurobj = new \CRM_Core_BAO_RecurringEntity();
    $dbParams = $recurobj->mapFormValuesToDB($params);

    $this->removeCurrentActionSchedule($event_id);
    $actionScheduleObj = \CRM_Core_BAO_ActionSchedule::add($dbParams);

    $recursion = new \CRM_Core_BAO_RecurringEntity();
    $recursion->dateColumns = $params['dateColumns'];
    $recursion->scheduleId = $actionScheduleObj->id;

    if (!empty($params['intervalDateColumns'])) {
      $recursion->intervalDateColumns = $params['intervalDateColumns'];
    }
    $recursion->entity_id = $params['entity_id'];
    $recursion->entity_table = $params['entity_table'];
    $linkedEntities = $this->getLinkedEntities($event_id);
    if (!empty($linkedEntities)) {
      $recursion->linkedEntities = $linkedEntities;
    }

    $recursion->generate();
  }

  /**
   * Remove a current schedule
   *
   * @param $event_id
   */
  protected function removeCurrentActionSchedule($event_id) {
    $sql = "SELECT id FROM civicrm_action_schedule WHERE used_for = 'civicrm_event' AND 'entity_value' = %1";
    $sqlParams[1] = arary($event_id, 'Integer');
    $id = \CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
    \CRM_Core_BAO_ActionSchedule::del($id);

    //If entity has any pre delete function, consider that first
    if (\CRM_Utils_Array::value('pre_delete_func', \CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event']) &&
      \CRM_Utils_Array::value('helper_class', \CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event'])
    ) {
      $preDeleteResult = call_user_func_array(\CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event']['pre_delete_func'], array($event_id));
      if (!empty($preDeleteResult)) {
        call_user_func(array(\CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event']['helper_class'], $preDeleteResult));
      }
    }
    //Ready to execute delete on entities if it has delete function set
    if (\CRM_Utils_Array::value('delete_func', \CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event']) &&
      \CRM_Utils_Array::value('helper_class', \CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event'])
    ) {
      //Check if pre delete function has some ids to be deleted
      if (!empty(\CRM_Core_BAO_RecurringEntity::$_entitiesToBeDeleted)) {
        foreach (\CRM_Core_BAO_RecurringEntity::$_entitiesToBeDeleted as $eid) {
          $result = civicrm_api3(
            'Event',
            \CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event']['delete_func'],
            array(
              'sequential' => 1,
              'id' => $eid,
            )
          );
          if ($result['error']) {
            throw new \Exception('Error during creating of repeating events');
          }
        }
      }
      else {
        $getRelatedEntities = \CRM_Core_BAO_RecurringEntity::getEntitiesFor($event_id, 'civicrm_event', FALSE);
        foreach ($getRelatedEntities as $key => $value) {
          $result = civicrm_api3(
            'Event',
            \CRM_Core_BAO_RecurringEntity::$_recurringEntityHelper['civicrm_event']['delete_func'],
            array(
              'sequential' => 1,
              'id' => $value['id'],
            )
          );
          if ($result['error']) {
            throw new \Exception('Error during creating of repeating events');
          }
        }
      }
    }

    // find all entities from the recurring set. At this point we 'll get entities which were not deleted
    // for e.g due to participants being present. We need to delete them from recurring tables anyway.
    $pRepeatingEntities = \CRM_Core_BAO_RecurringEntity::getEntitiesFor($event_id, 'civicrm_event');
    foreach ($pRepeatingEntities as $val) {
      \CRM_Core_BAO_RecurringEntity::delEntity($val['id'], $val['table'], TRUE);
    }
  }

  /**
   * Returns a list with linked entities
   *
   * @param $event_id
   *
   * @return array
   */
  protected function getLinkedEntities($event_id) {
    $linkedEntities = array(
      array(
        'table' => 'civicrm_price_set_entity',
        'findCriteria' => array(
          'entity_id' => $event_id,
          'entity_table' => 'civicrm_event',
        ),
        'linkedColumns' => array('entity_id'),
        'isRecurringEntityRecord' => FALSE,
      ),
      array(
        'table' => 'civicrm_uf_join',
        'findCriteria' => array(
          'entity_id' => $event_id,
          'entity_table' => 'civicrm_event',
        ),
        'linkedColumns' => array('entity_id'),
        'isRecurringEntityRecord' => FALSE,
      ),
      array(
        'table' => 'civicrm_tell_friend',
        'findCriteria' => array(
          'entity_id' => $event_id,
          'entity_table' => 'civicrm_event',
        ),
        'linkedColumns' => array('entity_id'),
        'isRecurringEntityRecord' => TRUE,
      ),
      array(
        'table' => 'civicrm_pcp_block',
        'findCriteria' => array(
          'entity_id' => $event_id,
          'entity_table' => 'civicrm_event',
        ),
        'linkedColumns' => array('entity_id'),
        'isRecurringEntityRecord' => TRUE,
      ),
    );
    return $linkedEntities;
  }
  
}