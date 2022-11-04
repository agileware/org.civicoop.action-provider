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

class GetRecurringEvent extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
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

    $repeatsBy = array(
      '1' => E::ts('Day of the month'),
      '2' => E::ts('Day of the week'),
    );

    $ends = array(
      '1' => ts('After'),
      '2' => ts('On'),
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

    $specs->addSpecification(new Specification('repeats_by', 'Integer', E::ts("Repeats by"), false, null, null, $repeatsBy));
    $specs->addSpecification(new Specification('ends', 'Integer', E::ts("Ends"), false, null, null, $ends));

    return $specs;
  }
  
  /**
   * Returns the specification of the output parameters of this action.
   * 
   * This function could be overridden by child classes.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('event_id', 'Integer', E::ts('Event ID')),
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
    $output->setParameter('event_id', $event_id);

    $entityId = $event_id;
    $checkParentExistsForThisId = \CRM_Core_BAO_RecurringEntity::getParentFor($event_id, 'civicrm_event');
    if ($checkParentExistsForThisId) {
      $entityId = $checkParentExistsForThisId;
    }
    $_scheduleReminderDetails = \CRM_Core_BAO_RecurringEntity::getReminderDetailsByEntityId($entityId, 'civicrm_event');
    if (property_exists($_scheduleReminderDetails, 'id')) {
      $output->setParameter('unit', $_scheduleReminderDetails->repetition_frequency_unit);
      $output->setParameter('interval', $_scheduleReminderDetails->repetition_frequency_interval);
      $output->setParameter('start_action_offset', $_scheduleReminderDetails->start_action_offset);

      $repeat_absolute_date = \CRM_Utils_Date::setDateDefaults($_scheduleReminderDetails->absolute_date);
      $output->setParameter('repeat_absolute_date', $repeat_absolute_date[0]);
      $output->setParameter('limit_to_day_of_month', $_scheduleReminderDetails->limit_to);

      if ($_scheduleReminderDetails->entity_status) {
        $explodeStartActionCondition = explode(" ", $_scheduleReminderDetails->entity_status);
        $output->setParameter('limit_to_day_of_week_no', $explodeStartActionCondition[0]);
        $output->setParameter('limit_to_day_of_week', $explodeStartActionCondition[1]);
      }

      $output->setParameter('repeats_by', 1);
      if ($_scheduleReminderDetails->entity_status) {
        $output->setParameter('repeats_by', 2);
      }
      $output->setParameter('ends', 1);
      if ($_scheduleReminderDetails->absolute_date) {
        $output->setParameter('ends', 2);
      }
    }

  }
  
}