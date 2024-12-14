<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Utils\SetOps;

use CRM_ActionProvider_ExtensionUtil as E;

class ContactIsInGroup extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $conditionGroupIdsCommaSeparated = $this->configuration->getParameter('groups');
    $conditionGroupIds = array_fill_keys(array_map('intval', explode(',', $conditionGroupIdsCommaSeparated)), 1);

    $contact_id = $parameterBag->getParameter('contact_id');
    $operator = $this->configuration->getParameter('operator');
    switch($operator) {
      case 'in one of':
        $isConditionValid = $this->contactHasOneOfGroups($contact_id, $conditionGroupIds);
        break;
      case 'in all of':
        $isConditionValid = $this->contactHasAllGroups($contact_id, $conditionGroupIds);
        break;
      case 'not in':
        $isConditionValid = $this->contactHasNotGroups($contact_id, $conditionGroupIds);
        break;
      default:
        throw new Exception("invalid operator: '$operator'");
    }
    return $isConditionValid;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $operatorOptions = [
      'in one of' => E::ts('Is in one of the selected groups'),
      'in all of' => E::ts('Is in all of the selected groups'),
      'not in' => E::ts('Is not in any of the selected groups'),
    ];
    return new SpecificationBag([
      new Specification('operator', 'String', E::ts('Operator'), true, 'in all of', null, $operatorOptions, FALSE),
      new Specification('groups', 'Integer', E::ts('Groups'), true, null, 'Group', null, true),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual condition.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID')),
    ]);
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Contact is in group');
  }

  /**
   * @param $contact_id
   * @param $group_ids
   *
   * @return bool
   */
  protected function contactHasOneOfGroups($contact_id, $condition_group_ids) {
    return !$this->contactHasNotGroups($contact_id, $condition_group_ids);
  }

  /**
   * @param $contact_id
   * @param $group_ids
   *
   * @return bool
   */
  protected function contactHasAllGroups($contact_id, $condition_group_ids) {
    /**
     * About the following if statement:
     *
     * If no groups were specified in the condition, from a set-theoretical point of view
     * the result should always be true, as the empty set is a subset of any set.
     * However, there is a pragmatic argument for changing this logic.
     *
     * Imagine you have defined perhaps one small-ish group in the condition and for
     * some reason that group disappears from it. The effect could be a very drastic
     * change, from an action that applies only to a small number of members to one
     * that applies to all contacts.
     *
     * Handling it this way breaks remains an irregularity. In every other situation,
     * the condition becomes more restrictive as more groups are added to it.
     * (Requiring that a contact has two groups is more restrictive than requiring
     * just one group, and so on.) Conversely, removing one of several groups from
     * the condition makes it less restrictive. Now, when the last group is taken away
     * from the condition, its "restrictiveness" jumps to being maximally strict
     * (the condition never passes).
     */
    if (SetOps::isEmpty($condition_group_ids)) {
      return false;
    }
    $contact_group_ids = $this->getContactGroups($contact_id);
    return SetOps::isSubset($condition_group_ids, $contact_group_ids);
  }

  /**
   * @param $contact_id
   * @param $group_ids
   *
   * @return bool
   */
  protected function contactHasNotGroups($contact_id, $condition_group_ids) {
    $contact_group_ids = $this->getContactGroups($contact_id);
    return SetOps::disjoint($contact_group_ids, $condition_group_ids);
  }

  /**
   * Get the groups a contact belongs to, as set in the form required by SetOps.
   * If the contact ID has gone missing for some reason (e.g. the user forgot to
   * define the parameter mapping), the current code returns all groups. It is
   * not clear how this should be handled.
   *
   * @param contact_id
   */
  protected function getContactGroups($contact_id) {
    if ($contact_id === NULL) {
      \Civi::log()->error("No contact ID was passed as a parameter to ContactIsInGroup. Check the parameter mapping. Form processor ID = " . $this->getMetaData()->getParameter("form_processor_id"));
    }
    return \CRM_Contact_BAO_GroupContact::getGroupList($contact_id);
  }

}
