<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupByNameSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\SpecificationGroup;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateActivity extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $subject = new Specification('subject', 'String', E::ts('Default Subject'));
    $subject->setDescription(E::ts('Subject when you don\'t get use a parameter for the subejct.'));
    return new SpecificationBag([
      new OptionGroupByNameSpecification('activity_type', 'activity_type', E::ts('Activity Type'), FALSE),
      new OptionGroupByNameSpecification('activity_status', 'activity_status', E::ts('Activity Status'), TRUE),
      $subject,
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $subject = new Specification('subject', 'String', E::ts('Subject'));
    $subject->setDescription(E::ts('Use this field when you want to set the subject from another field.'));
    $bag = new SpecificationBag([
      new Specification('source_contact_id', 'Integer', E::ts('Source Contact ID'), TRUE,null, null, null, false),
      new Specification('target_contact_id', 'Integer', E::ts('Target Contact ID'), TRUE,null, null, null, true),
      new Specification('assignee_contact_id', 'Integer', E::ts('Assignee Contact ID'), FALSE, null, null, null, false),
      new Specification('activity_type_id', 'Integer', E::ts('Activity Type'), FALSE, null, null, null, FALSE),
      new Specification('activity_date', 'Timestamp', E::ts('Activity Date'), FALSE),
      new Specification('id', 'Integer', E::ts('Activity ID'), false),
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), false),
      $subject,
      new Specification('details', 'Text', E::ts('Details'), false),
      new Specification('case_id', 'Integer', E::ts('Case ID'), false),
    ]);

    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Activity',
      'is_active' => 1,
      'options' => ['limit' => 0],
    ]);
    foreach ($customGroups['values'] as $customGroup) {
      $bag->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
    }

    return $bag;
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
      new Specification('id', 'Integer', E::ts('Activity record ID')),
    ));
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Get the contact and the event.

    $activityParams['status_id'] = $this->configuration->getParameter('activity_status');
    if ($parameters->doesParameterExists('subject')) {
      $activityParams['subject'] = $parameters->getParameter('subject');
    } elseif ($this->configuration->getParameter('subject')) {
      $activityParams['subject'] = $this->configuration->getParameter('subject');
    }

    if ($parameters->doesParameterExists('id')) {
      $activityParams['id'] = $parameters->getParameter('id');
    }
    $activityParams['source_contact_id'] = $parameters->getParameter('source_contact_id');
    $activityParams['target_contact_id'] = $parameters->getParameter('target_contact_id');
    if ($parameters->doesParameterExists('assignee_contact_id')) {
      $activityParams['assignee_contact_id'] = $parameters->getParameter('assignee_contact_id');
    }
    $activityParams['activity_date_time'] = $parameters->getParameter('activity_date');
    if ($parameters->doesParameterExists('campaign_id')) {
      $activityParams['campaign_id'] = $parameters->getParameter('campaign_id');
    }
    if ($parameters->doesParameterExists('details')) {
      $activityParams['details'] = $parameters->getParameter('details');
    }
    if ($parameters->doesParameterExists('activity_type_id')) {
      $activityParams['activity_type_id'] = $parameters->getParameter('activity_type_id');
    } elseif ($activityParams['activity_type_id'] = $this->configuration->doesParameterExists('activity_type')) {
      $activityParams['activity_type_id'] = $this->configuration->getParameter('activity_type');
    }
    if ($parameters->doesParameterExists('case_id')) {
      $activityParams['case_id'] = $parameters->getParameter('case_id');
    }

    $activityParams = array_merge($activityParams, CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification()));

    try {
      // Do not use api as the api checks for an existing relationship.
      $result = civicrm_api3('Activity', 'Create', $activityParams);
      $output->setParameter('id', $result['id']);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }



}
