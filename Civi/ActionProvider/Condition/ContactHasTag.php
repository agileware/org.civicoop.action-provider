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

class ContactHasTag extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $contactId = $parameterBag->getParameter('contact_id');
    if ($contactId === NULL) {
      \Civi::log()->error("No contact ID was passed as a parameter to ContactHasTag. Check the parameter mapping. Form processor ID = " . $this->getMetaData()->getParameter("form_processor_id"));
      return false;
    }
    $conditionTagIds = $this->getConditionTagIds();
    $operator = $this->configuration->getParameter('operator');
    switch($operator) {
      case 'in one of':
        $isConditionValid = $this->contactHasOneOfTags($contactId, $conditionTagIds);
        break;
      case 'in all of':
        $isConditionValid = $this->contactHasAllTags($contactId, $conditionTagIds);
        break;
      case 'not in':
        $isConditionValid = $this->contactHasNoneOfTags($contactId, $conditionTagIds);
        break;
      default:
        throw new Exception("invalid operator: '$operator'");
    }
    return $isConditionValid;
  }

  /**
   * Returns the IDs of the tags in this condition, in the format used in SetOps
   *
   * @return array
   */
  protected function getConditionTagIds()
  {
    $conditionTagNames = $this->configuration->getParameter('tags');
\Civi::log()->debug("condition tag names: " . var_export($conditionTagNames, TRUE));
    $conditionTagIds = SetOps::emptySet();
    foreach($conditionTagNames as $tagName) {
      try {
        $tagId = civicrm_api3('Tag', 'getvalue', [
          'return' => 'id',
          'name' => $tagName,
          'used_for' => 'Contacts'
        ]);
        SetOps::add($conditionTagIds, $tagId, $tagName);
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    return $conditionTagIds;
  }

  /**
   * Returns the specification of the configuration options for this condition
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $operatorOptions = [
      'in one of' => E::ts('Has one of the selected tags'),
      'in all of' => E::ts('Has all of the selected tags'),
      'not in' => E::ts('Does not have any of the selected tags'),
    ];
    return new SpecificationBag([
      new Specification('operator', 'String', E::ts('Operator'), true, 'in all of', null, $operatorOptions, FALSE),
      new Specification('tags', 'Integer', E::ts('Tags'), true, null, null, $this->getAllTags(), true),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual condition
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
    return E::ts('Contact has tag');
  }

  /**
   * @param $contactId
   * @param $conditionTagIds
   *
   * @return bool
   */
  protected function contactHasNoneOfTags($contactId, $conditionTagIds) {
    $contactTagIds = $this->getContactTags($contactId);
    return SetOps::disjoint($contactTagIds, $conditionTagIds);
  }

  /**
   * @param $contactId
   * @param $conditionTagIds
   *
   * @return bool
   */
  protected function contactHasAllTags($contactId, $conditionTagIds) {
    /**
     * About the following if statement:
     *
     * If no tags were specified in the condition, from a set-theoretical point of view
     * the result should always be true, as the empty set is a subset of any set.
     * However, there is a pragmatic argument for changing this logic.
     *
     * Imagine you have defined perhaps one small-ish tag (in terms of number of tagged contacts)
     * in the condition and for some reason that tag disappears from it. The effect could be
     * a very drastic change, from an action that applies only to a small number of tagged contacts
     * to one that applies to all contacts.
     *
     * Handling it this way still is an irregularity. In every other situation,
     * the condition becomes more restrictive as more tags are added to it.
     * (Requiring that a contact has two tags is more restrictive than requiring
     * just one tag, and so on.) Conversely, removing one of several tags from
     * the condition makes it less restrictive. Now, when the last tag is taken away
     * from the condition, its "restrictiveness" jumps to being maximally strict
     * (the condition never passes).
     */
    if (SetOps::isEmpty($conditionTagIds)) {
      return false;
    }
    $contactTagIds = $this->getContactTags($contactId);
    return SetOps::isSubset($conditionTagIds, $contactTagIds);
  }

  /**
   * @param $contactId
   * @param $conditionTagIds
   *
   * @return bool
   */
  protected function contactHasOneOfTags($contactId, $conditionTagIds) {
    return !$this->contactHasNoneOfTags($contactId, $conditionTagIds);
  }

  /**
   * Method to get tags
   *
   * @return array
   * @access protected
   */
  protected function getAllTags() {
    $apiParams['used_for'] = 'Contacts';
    $apiParams['options']['limit'] = 0;
    $options = [];
    $api = civicrm_api3('Tag', 'get', $apiParams);
    foreach($api['values'] as $tag) {
      $options[$tag['name']] = $tag['name'];
    }
    asort($options);
    return $options;
  }

  /**
   * Returns the IDs for the tags of a given contact, in the format used in SetOps
   *
   * @param contactId
   */
  protected function getContactTags($contactId) {
     $tagArray = \CRM_Core_BAO_EntityTag::getTag($contactId);
     return SetOps::setFromArray($tagArray);
  }

}
