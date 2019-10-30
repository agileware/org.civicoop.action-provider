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

use Civi\ActionProvider\Parameter\SpecificationCollection;
use CRM_ActionProvider_ExtensionUtil as E;

class CheckParameters extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $function = $this->configuration->getParameter('function');
    $parameters = $parameterBag->getParameter('parameters');
    foreach($parameters as $parameter) {
      $value = $parameter->getParameter('parameter');
      switch ($function) {
        case 'are not empty':
          if (empty($value)) {
            return false;
          }
          break;
        case 'are empty':
          if (!empty($value)) {
            return false;
          }
          break;
      }
    }
    return true;
  }

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('function', 'String', E::ts('Condition'), true, 'are not empty', null, array(
        'are not empty' => E::ts('Are not empty'),
        'are empty' => E::ts('Are empty'),
      ))
    ));
  }

  /**
   * Returns the specification of the parameters of the actual condition.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $parametersBag = new SpecificationBag(array(
      new Specification('parameter', 'String', E::ts('Parameter'), true, null, null, null, true),
    ));
    return new SpecificationBag(array(
      new SpecificationCollection('parameters', E::ts('Parameters'), $parametersBag, 1),
    ));
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Parameters are (not) empty');
  }

}
