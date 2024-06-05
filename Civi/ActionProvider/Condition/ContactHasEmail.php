<?php
/**
 * @author Johannes Margraf <margraf@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class ContactHasEmail extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $email = $parameterBag->getParameter('email');
    $contact_id = $parameterBag->getParameter('contact_id');

    try {
      $return_count = civicrm_api3('Email', 'getcount', [
        'return' => 'id',
        'email' => $email,
        'contact_id' => $contact_id
      ]);
      if ($return_count>=1){
          // Found an Email
          if ($this->configuration->getParameter('operator') == 1){
              // Operator is "has email"
              return TRUE;
          } else{
              // Operator is "does not have email"
              return FALSE;
          }
      } else{
          // No Email found
          if ($this->configuration->getParameter('operator') == 1){
              // Operator is "has email"
              return FALSE;
          } else{
              // Operator is "does not have email"
              return TRUE;
          }
      }
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $operatorOptions = array(
      1 => E::ts('Contact has Email'),
      0 => E::ts('Contact does not have Email'),
    );
    return new SpecificationBag(array(
      new Specification('operator', 'Boolean', E::ts('Operator'), true, 'has', null, $operatorOptions, FALSE),
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
      new Specification('email', 'Integer', E::ts('Email'), TRUE),
    ));
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Contact has email');
  }

}
