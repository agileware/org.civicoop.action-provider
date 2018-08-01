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

class ParameterIsEmpty extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $parameter = $parameterBag->getParameter('parameter');
    if (empty($parameter)) {
      return true;
    }
    return false;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual condition.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('parameter', 'String', E::ts('Parameter')),
    ));
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Parameter is empty');
  }

}