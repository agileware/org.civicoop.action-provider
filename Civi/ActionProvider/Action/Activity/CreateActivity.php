<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
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
    return new SpecificationBag([
      new OptionGroupSpecification('activity_type', 'activity_type', E::ts('Activity Type'), TRUE),
      new OptionGroupSpecification('activity_status', 'activity_status', E::ts('Activity Status'), TRUE),
      new Specification('subject', 'String', E::ts('Subject')),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('source_contact_id', 'Integer', E::ts('Source Contact ID'), TRUE),
      new Specification('target_contact_id', 'Integer', E::ts('Target Contact ID'), TRUE),
      new Specification('assignee_contact_id', 'Integer', E::ts('Assignee Contact ID'), FALSE),
      new Specification('activity_date', 'Date', E::ts('Activity Date'), TRUE),
      new Specification('id', 'Integer', E::ts('Activity ID'), false),
      new Specification('campaign_id', 'Integer', E::ts('Campaign'), false),
    ]);

    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Activity',
      'is_active' => 1,
      'options' => ['limit' => 0],
    ]);
    foreach ($customGroups['values'] as $customGroup) {
      $customFields = civicrm_api3('CustomField', 'get', [
        'custom_group_id' => $customGroup['id'],
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach ($customFields['values'] as $customField) {
        $spec = CustomField::getSpecFromCustomField($customField, $customGroup['title'] . ': ', FALSE);
        if ($spec) {
          $bag->addSpecification($spec);
        }
      }
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
    $activityParams['activity_type_id'] = $this->configuration->getParameter('activity_type');
    $activityParams['status_id'] = $this->configuration->getParameter('activity_status');
    if ($this->configuration->getParameter('subject')) {
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

    foreach($this->getParameterSpecification() as $spec) {
      if (stripos($spec->getName(), 'custom_')!==0) {
        continue;
      }
      if ($parameters->doesParameterExists($spec->getName())) {
        $activityParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
      }
    }

    try {
      // Do not use api as the api checks for an existing relationship.
      $result = civicrm_api3('Activity', 'Create', $activityParams);
      $output->setParameter('id', $result['id']);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }



}