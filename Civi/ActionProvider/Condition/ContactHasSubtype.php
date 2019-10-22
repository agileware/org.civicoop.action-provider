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

class ContactHasSubtype extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $contact_type = civicrm_api3('ContactType', 'getsingle', array('name' => $this->configuration->getParameter('contact_type')));
    $contact_sub_type = false;
    if (isset($contact_type['parent_id']) && $contact_type['parent_id'] > 0) {
      $contact_sub_type = $contact_type;
      $contact_type = civicrm_api3('ContactType', 'getsingle', array('id' => $contact_sub_type['parent_id']));
    }

    $apiParams['id'] = $parameterBag->getParameter('contact_id');
    $apiParams['contact_type'] = $contact_type['name'];
    if ($contact_sub_type) {
      $apiParams['contact_sub_type'] = $contact_sub_type['name'];
    }
    try {
      $contact = civicrm_api3('Contact', 'getsingle', $apiParams);
      return true;
    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing
    }
    return false;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $contactTypesApi = civicrm_api3('ContactType', 'get', array('options' => array('limit' => 0)));
    $contactTypes = array();
    foreach($contactTypesApi['values'] as $contactType) {
      $contactTypes[$contactType['name']] = $contactType['label'];
    }
    return new SpecificationBag(array(
      new Specification('contact_type', 'Integer', E::ts('Contact type'), true, null, null, $contactTypes, FALSE),
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
    return E::ts('Contact has (sub)type');
  }

}
