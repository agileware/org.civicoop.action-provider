<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class ContactHasTag extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $tagNames = $this->configuration->getParameter('tags');
    $tagIds = array();
    foreach($tagNames as $tagName) {
      try {
        $tagId = civicrm_api('Tag', 'getvalue', [
          'return' => 'id',
          'name' => $tagName,
          'used_for' => 'Contacts'
        ]);
        $tagIds[] = $tagId;
      } catch (\Exception $e) {
        // Do nothing
      }
    }

    $isConditionValid = true;
    switch($this->configuration->getParameter('operator')) {
      case 'in one of':
        $isConditionValid = $this->contactHasOneOfTags($parameterBag->getParameter('contact_id'), $tagIds);
        break;
      case 'in all of':
        $isConditionValid = $this->contactHasAllTags($parameterBag->getParameter('contact_id'), $tagIds);
        break;
      case 'not in':
        $isConditionValid = $this->contactHasNotTag($parameterBag->getParameter('contact_id'), $tagIds);
        break;
    }
    return $isConditionValid;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $operatorOptions = array(
      'in one of' => E::ts('Has one of the selected tags'),
      'in all of' => E::ts('Has all of the selected tags'),
      'not in' => E::ts('Does not have any of the selected tags'),
    );
    return new SpecificationBag(array(
      new Specification('operator', 'String', E::ts('Operator'), true, 'in all of', null, $operatorOptions, FALSE),
      new Specification('tags', 'Integer', E::ts('Tags'), true, null, null, $this->getTags(), true),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual condition.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID')),
    ));
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Contact has tag');
  }

  /**
   * @param $contact_id
   * @param $tag_ids
   *
   * @return bool
   */
  protected function contactHasNotTag($contact_id, $tag_ids) {
    $isValid = true;

    $tags = CRM_Core_BAO_EntityTag::getTag($contact_id);
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid = false;
      }
    }

    return $isValid;
  }

  /**
   * @param $contact_id
   * @param $tag_ids
   *
   * @return bool
   */
  protected function contactHasAllTags($contact_id, $tag_ids) {
    $isValid = 0;

    $tags = \CRM_Core_BAO_EntityTag::getTag($contact_id);
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid++;
      }
    }

    if (count($tag_ids) == $isValid && count($tag_ids) > 0) {
      return true;
    }

    return false;
  }

  /**
   * @param $contact_id
   * @param $tag_ids
   *
   * @return bool
   */
  protected function contactHasOneOfTags($contact_id, $tag_ids) {
    $isValid = false;

    $tags = \CRM_Core_BAO_EntityTag::getTag($contact_id);
    foreach($tag_ids as $tag_id) {
      if (in_array($tag_id, $tags)) {
        $isValid = true;
        break;
      }
    }

    return $isValid;
  }

  /**
   * Method to get tags
   *
   * @return array
   * @access protected
   */
  protected function getTags() {
    $apiParams['used_for'] = 'Contacts';
    $apiParams['options']['limit'] = 0;
    $options = array();
    $api = civicrm_api3('Tag', 'get', $apiParams);
    foreach($api['values'] as $tag) {
      $options[$tag['name']] = $tag['name'];
    }
    asort($options);
    return $options;
  }



}
