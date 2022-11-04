<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Group;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetGroupSubscriptions extends AbstractAction {

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
    $contact_id = $parameters->getParameter('contact_id');
    $subscribedGroupIds = [];
    $apiParams['contact_id'] = $contact_id;
    $apiParams['options']['limit'] = 0;
    if ($this->configuration->doesParameterExists('group_ids')) {
      $groupsToCheck = explode(",", $this->configuration->getParameter('group_ids'));
      foreach($groupsToCheck as $group_id) {
        $apiParams['group_id'] = $group_id;
        try {
          $subscribedGroups = civicrm_api3('GroupContact', 'get', $apiParams);
          foreach($subscribedGroups['values'] as $value) {
            $subscribedGroupIds[] = $value['group_id'];
          }
        } catch (\CiviCRM_API3_Exception $ex) {
          // Do nothing.
        }
      }
    } else {
      try {
        $subscribedGroups = civicrm_api3('GroupContact', 'get', $apiParams);
        foreach($subscribedGroups['values'] as $value) {
          $subscribedGroupIds[] = $value['group_id'];
        }
      } catch (\CiviCRM_API3_Exception $ex) {
        // Do nothing.
      }
    }

    $output->setParameter('group_ids', $subscribedGroupIds);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('group_ids', 'Integer', E::ts('Subscribe/Unsubscribe from group'), false, null, 'Group', null, TRUE),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('group_ids', 'Integer', E::ts('Group IDs'), true, null, 'Group', null, TRUE),
    ));
  }


  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText() {
    return E::ts('This action will return a list of the groups to which a user is subscribed. You have to specify which groups you want to check.');
  }

}
