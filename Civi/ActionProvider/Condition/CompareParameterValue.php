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

class CompareParameterValue extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $parameter = $parameterBag->getParameter('parameter');
    $comparison = $this->configuration->getParameter('comparison');
    $value = $this->configuration->getParameter('value');
    switch ($comparison) {
      case '>':
        return ($parameter > $value) ? true : false;
        break;
      case '>=':
        return ($parameter >= $value) ? true : false;
        break;
      case '<':
        return ($parameter < $value) ? true : false;
        break;
      case '<=':
        return ($parameter <= $value) ? true : false;
        break;
      case '=':
        return ($parameter == $value) ? true : false;
        break;
      case '!=':
        return ($parameter != $value) ? true : false;
        break;
    }
    return false;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('value', 'String', E::ts('Value'), true),
      new Specification('comparison', 'String', E::ts('Comparison'), true, null, null, [
        '>' => E::ts('Greater than'),
        '>=' => E::ts('Greater than or equal to'),
        '<=' => E::ts('Lesser than or equal to'),
        '<' => E::ts('Lesser than'),
        '=' => E::ts('Equal to'),
        '!=' => E::ts('Not Equal to'),
      ]),
    ));
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
    return E::ts('Compare parameter value');
  }

}
