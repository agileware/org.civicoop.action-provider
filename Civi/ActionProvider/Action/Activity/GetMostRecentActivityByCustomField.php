<?php
/**
 * Gets the most recent activity of a contact. So the contact_id is the key, the
 * activity id is returned.
 *
 * Returns the activity id and the contact id as well.
 *
 * @author Klaas Eikelboom  <klaas.eikelboom@civicoop.org>
 * @date 25-May-2020
 * @license  AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_Exception;

class GetMostRecentActivityByCustomField extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $fields = array();
    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Activity');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $customFields = $config->getCustomFieldsOfCustomGroup($customGroup['id']);
        foreach($customFields as $customField) {
          $fields[$customField['id']] = $customGroup['title']. ' :: '.$customField['label'];
        }
      }
    }

    $bag = new SpecificationBag();
    $bag->addSpecification(new Specification('custom_field', 'Integer', E::ts('Custom field'), true, null, null, $fields, false));
    $bag->addSpecification(new OptionGroupSpecification('activity_type_id', 'activity_type', E::ts('Activity Type'), false, null, true));
    $bag->addSpecification(new OptionGroupSpecification('status_id', 'activity_status', E::ts('Activity Status'), false, null, true));
    $bag->addSpecification(new Specification('error', 'Boolean', E::ts('Error on no activity found'), false, false));
    return $bag;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('value', 'String', E::ts('Custom Field Value'), true),
    ]);
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
    $bag = new SpecificationBag();
    $bag->addSpecification(new Specification('activity_id', 'Integer', E::ts('Activity ID')));
    return $bag;
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
   * @throws \Civi\ActionProvider\Exception\ExecutionException
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $customFieldId = $this->configuration->getParameter('custom_field');
    $activity_type_ids = $this->configuration->getParameter('activity_type_id');
    $status_ids = $this->configuration->getParameter('status_id');
    $error = $this->configuration->getParameter('error');

    $apiParams = [];
    $apiParams['custom_'.$customFieldId] = $parameters->getParameter('value');
    if ($activity_type_ids && is_array($activity_type_ids) && count($activity_type_ids)) {
      $apiParams['activity_type_id']['IN'] = $activity_type_ids;
    } elseif ($activity_type_ids && !is_array($activity_type_ids)) {
      $apiParams['activity_type_id']['IN'] = explode(",", $activity_type_ids);
    }
    if ($status_ids && is_array($status_ids) && count($status_ids)) {
      $apiParams['status_id']['IN'] = $status_ids;
    } elseif ($status_ids && !is_array($status_ids)) {
      $apiParams['status_id']['IN'] = explode(",", $status_ids);
    }
    $apiParams['options']['sort'] = 'activity_date_time DESC';
    $apiParams['options']['limit'] = 1;
    $apiParams['return'] = 'id';
    try {
      $activityId = civicrm_api3('Activity', 'getvalue', $apiParams);
      $output->setParameter('activity_id', $activityId);
    }
    catch (CRM_Core_Exception $e) {
      if ($error && empty($activity_id)) {
        throw new ExecutionException(E::ts('Could not find an activity'));
      }
    }
  }
}
