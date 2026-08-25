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
use Civi\ActionProvider\Exception\ExecutionException;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateGroupSubscriptions extends AbstractAction {

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
    $groups = $parameters->getParameter('group_ids');
    $groupsToCheck = $this->configuration->getParameter('group_ids');
    foreach ($groupsToCheck as $groupIdToCheck) {
      $params = [
        'contact_id' => $parameters->getParameter('contact_id'),
        'group_id' => $groupIdToCheck,
      ];
      $groupContact = civicrm_api3('GroupContact', 'get', $params);
      if (in_array($groupIdToCheck, $groups) && $groupContact['count'] == 0) {
        civicrm_api3('GroupContact', 'create', $params);
      }
      elseif (!in_array($groupIdToCheck, $groups) && $groupContact['count'] > 0) {
        $params['status'] = 'Removed';
        civicrm_api3('GroupContact', 'create', $params);
      }
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('group_ids', 'Integer', E::ts('Subscribe/Unsubscribe from group'), true, null, 'Group', null, TRUE),
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
    return E::ts('This action will subscribe/unsubscribe a contact from a certain set of groups. The parameter Group IDs is a list of which a contact needs to be subscribed to, the groups missing from the list are the groups from which the contact will be unsubscribed.');
  }

}
