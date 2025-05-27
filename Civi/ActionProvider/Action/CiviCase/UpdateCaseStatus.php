<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupByNameSpecification;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateCaseStatus extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new OptionGroupSpecification('status_id', 'case_status', E::ts('Case Status'), false),
      new Specification('log_activity', 'Boolean', E::ts('Log Case Change to Activity'), true, 0),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Case ID'), true),
      new Specification('status_id', 'Integer', E::ts('Status ID'), false),
      new Specification('reporter_contact_id', 'Integer', E::ts('Reporter Contact ID'), false),
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
    return new SpecificationBag(array());
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
    $caseParams['id'] = $parameters->getParameter('id');
    if ($parameters->getParameter('status_id')) {
      $caseParams['status_id'] = $parameters->getParameter('status_id');
    } else {
      $caseParams['status_id'] = $this->configuration->getParameter('status_id');
    }
    $activityParams = [];
    if($this->configuration->doesParameterExists('log_activity') && $this->configuration->getParameter('log_activity')) {
      $case = civicrm_api3('Case', 'getsingle', ['id' => $parameters->getParameter('id')]);
      // log only when the status actually changes
      if ($case['status_id'] <> $caseParams['status_id']) {
        $statusLabels = \CRM_Case_PseudoConstant::caseStatus();
        $activityParams['subject'] = ts('Case status changed from %1 to %2', [
          1 => $statusLabels[$case['status_id']] ?? NULL,
          2 => $statusLabels[$caseParams['status_id']] ?? NULL,
        ]);
        $activityParams['activity_type_id'] = "Change Case Status";
        if($parameters->doesParameterExists('reporter_contact_id') && !empty($parameters->getParameter('reporter_contact_id'))) {
          $activityParams['source_contact_id'] = $parameters->getParameter('reporter_contact_id');
        } else {
          $activityParams['source_contact_id'] = 'user_contact_id';
        }
        $activityParams['case_id'] = $case['id'];
        $activityParams['target_id'] = $case['client_id'];
      }

    }
    try {
      // Do not use api as the api checks for an existing relationship.
      $result = civicrm_api3('Case', 'Create', $caseParams);
      if(!empty($activityParams)){
        $resultActivity =civicrm_api3('Activity', 'Create', $activityParams);
      }
    } catch (\Exception $e) {
      // Do nothing.
    }
  }

  public function getHelpText() {
    return E::ts('Set the <i>Log Case Change to Activity</i> if you want to have a Case Change Activity in the same way as in the userinterface.');
  }



}
