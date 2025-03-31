<?php
/**
 * @author Sebastian Lisken <sebastian.lisken@civiservice.de>
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class ContactIsInGroup extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $contactId = $parameterBag->getParameter('contact_id');
    if ($contactId === NULL) {
      \Civi::log()->error("No contact ID was passed as a parameter to ContactIsInGroup. Check the parameter mapping. Form processor ID = " . $this->getMetaData()->getParameter("form_processor_id"));
      return false;
    }
    $conditionGroupIds = $this->getConditionGroupIds();
    $operator = $this->configuration->getParameter('operator');
    switch($operator) {
      case 'in one of':
        $isConditionValid = $this->contactHasOneOfGroups($contactId, $conditionGroupIds);
        break;
      case 'in all of':
        $isConditionValid = $this->contactHasAllGroups($contactId, $conditionGroupIds);
        break;
      case 'not in':
        $isConditionValid = $this->contactHasNoneOfGroups($contactId, $conditionGroupIds);
        break;
      default:
        throw new Exception("invalid operator: '$operator'");
    }
    return $isConditionValid;
  }

  private function getConditionGroupIds()
  {
    $conditionGroupIdsCommaSeparated = $this->configuration->getParameter('groups');
    return array_map('intval', explode(',', $conditionGroupIdsCommaSeparated));
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
   * @param $contactId
   * @param $conditionGroupIds
   *
   * @return bool
   */
  protected function contactHasOneOfGroups($contactId, $conditionGroupIds) {
    $query = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $contactId)
      ->addWhere('groups', 'IN', $conditionGroupIds);
    return count($query->execute()) != 0;
  }

  /**
   * @param $contactId
   * @param $conditionGroupIds
   *
   * @return bool
   */
  protected function contactHasAllGroups($contactId, $conditionGroupIds) {
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
     * Handling it this way still is an irregularity. In every other situation,
     * the condition becomes more restrictive as more groups are added to it.
     * (Requiring that a contact has two groups is more restrictive than requiring
     * just one group, and so on.) Conversely, removing one of several groups from
     * the condition makes it less restrictive. Now, when the last group is taken away
     * from the condition, its "restrictiveness" jumps to being maximally strict
     * (the condition never passes).
     */
    if (!$conditionGroupIds) {
      return false;
    }
    $query = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $contactId);
    foreach ($conditionGroupIds as $conditionGroupId) {
      $query = $query->addWhere('groups', 'IN', [$conditionGroupId]);
    }
    return count($query->execute()) != 0;
  }

  /**
   * @param $contactId
   * @param $conditionGroupIds
   *
   * @return bool
   */
  protected function contactHasNoneOfGroups($contactId, $conditionGroupIds) {
    return !$this->contactHasOneOfGroups($contactId, $conditionGroupIds);
  }

}
