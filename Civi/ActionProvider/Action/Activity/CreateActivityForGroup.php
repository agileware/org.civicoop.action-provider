<?php

namespace Civi\ActionProvider\Action\Activity;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;


use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\OptionGroupByNameSpecification;
use Civi\ActionProvider\Parameter\SpecificationGroup;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateActivityForGroup extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $subject = new Specification('subject', 'String', E::ts('Default Subject'));
    $subject->setDescription(E::ts('Subject when you don\'t get use a parameter for the subejct.'));
    return new SpecificationBag([
      new Specification('group_id', 'Integer', E::ts('Select group'), false, null, 'Group', null, FALSE),
      new Specification('group_title', 'String', E::ts('Or group title'), false, null, null, null, FALSE),
      new OptionGroupByNameSpecification('activity_type', 'activity_type', E::ts('Activity Type'), FALSE),
      new OptionGroupByNameSpecification('activity_status', 'activity_status', E::ts('Activity Status'), TRUE),
      $subject,
      new OptionGroupByNameSpecification('priority', 'priority', E::ts('Priority'), TRUE, 'Normal'),
    ]);
    return new SpecificationBag(array(
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $subject = new Specification('subject', 'String', E::ts('Subject'));
    $subject->setDescription(E::ts('Use this field when you want to set the subject from another field.'));
    $bag = new SpecificationBag([
      new Specification('group_id', 'Integer', E::ts('Group ID'), FALSE, null, null, null, false),
      new Specification('source_contact_id', 'Integer', E::ts('Source Contact ID'), TRUE, null, null, null, false),
      new Specification('target_contact_id', 'Integer', E::ts('Target Contact ID'), TRUE, null, null, null, true),
      new Specification('activity_type_id', 'Integer', E::ts('Activity Type'), FALSE, null, null, null, FALSE),
      new Specification('location', 'String', E::ts('Location'), FALSE),
      new Specification('activity_date', 'Timestamp', E::ts('Activity Date'), FALSE),
      new Specification('duration', 'Integer', E::ts('Duration'), FALSE),
      new Specification('id', 'Integer', E::ts('Activity ID'), false),
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), false),
      $subject,
      new Specification('details', 'Text', E::ts('Details'), false),
      new Specification('case_id', 'Integer', E::ts('Case ID'), false),
    ]);

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Activity');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $bag->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }

    return $bag;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
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


    // Create and collect tthe activity configurations
    $activityParams['status_id'] = $this->configuration->getParameter('activity_status');
    $activityParams['priority_id'] = $this->configuration->getParameter('priority');
    if ($parameters->doesParameterExists('subject')) {
      $activityParams['subject'] = $parameters->getParameter('subject');
    }
    elseif ($this->configuration->getParameter('subject')) {
      $activityParams['subject'] = $this->configuration->getParameter('subject');
    }

    if ($parameters->doesParameterExists('id')) {
      $activityParams['id'] = $parameters->getParameter('id');
    }
    $activityParams['source_contact_id'] = $parameters->getParameter('source_contact_id');
    $activityParams['target_contact_id'] = $parameters->getParameter('target_contact_id');

    $activityParams['location'] = $parameters->getParameter('location');
    $activityParams['activity_date_time'] = $parameters->getParameter('activity_date');
    $activityParams['duration'] = $parameters->getParameter('duration');

    if ($parameters->doesParameterExists('campaign_id')) {
      $activityParams['campaign_id'] = $parameters->getParameter('campaign_id');
    }
    if ($parameters->doesParameterExists('details')) {
      $activityParams['details'] = $parameters->getParameter('details');
    }
    if ($parameters->doesParameterExists('activity_type_id')) {
      $activityParams['activity_type_id'] = $parameters->getParameter('activity_type_id');
    }
    elseif ($activityParams['activity_type_id'] = $this->configuration->doesParameterExists('activity_type')) {
      $activityParams['activity_type_id'] = $this->configuration->getParameter('activity_type');
    }
    if ($parameters->doesParameterExists('case_id')) {
      $activityParams['case_id'] = $parameters->getParameter('case_id');
    }

    if (empty($activityParams['activity_type_id']) && !empty($activityParams['id'])) {
      $activityParams['activity_type_id'] = civicrm_api3('Activity', 'getvalue', ['return' => 'activity_type_id', 'id' => $activityParams['id'], 'options' => ['limit' => 1]]);
    }

    $activityParams = array_merge($activityParams, CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification()));

    // get list of contacts in the provided group
    if ($parameters->doesParameterExists('group_id')) {
      $groupId = $parameters->getParameter('group_id');
    }
    else if ($this->configuration->doesParameterExists('group_id')) {
      $groupId = $this->configuration->getParameter('group_id');
    }
    else if ($this->configuration->doesParameterExists('group_title')) {
      $groupTitle = $this->configuration->getParameter('group_title');
      $groupId = civicrm_api4('Groups', 'get', [
        'select' => ['id'],
        'where' => [
          ['title', '=', $groupTitle]
        ]]
      );
      $groupId = $groupId[0]['id'];
    }
    else {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not create activity. No group could be found.'));
    }
    $groupContacts = civicrm_api4('GroupContact', 'get', array(
      'select' => array('contact_id'),
      'where' => array(
        array('group_id', '=', $groupId),
        array('status', '=', 'Added')
      )
    ));

    foreach ($groupContacts as $contact) {
      $activityParams['assignee_contact_id'] = $contact['contact_id'];
      try {
        // Do not use api as the api checks for an existing relationship.
        $result = civicrm_api3('Activity', 'Create', $activityParams);
        //$output->setParameter('id', $result['id']);
      }
      catch (\Exception $e) {
        \Civi::log()->error('ActionProvider CreateActivity Error: ' . $e->getMessage());
        throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not create activity.'));
      }
    }
  }


}