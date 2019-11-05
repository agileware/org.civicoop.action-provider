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

    switch ($function) {
      case 'all are not empty':
        return $this->allAreNotEmpty($parameters);
        break;
      case 'one of is not empty':
        return $this->oneOfIsNotEmpty($parameters);
        break;
      case 'all are empty':
        return $this->allAreEmpty($parameters);
        break;
    }
    return true;
  }

  private function allAreEmpty($parameters) {
    foreach($parameters as $parameter) {
      $value = $parameter->getParameter('parameter');
      if (!empty($value)) {
        return false;
      }
    }
    return true;
  }

  private function allAreNotEmpty($parameters) {
    foreach($parameters as $parameter) {
      $value = $parameter->getParameter('parameter');
      if (empty($value)) {
        return false;
      }
    }
    return true;
  }

  private function oneOfIsNotEmpty($parameters) {
    $allAreEmpty = false;
    foreach($parameters as $parameter) {
      $value = $parameter->getParameter('parameter');
      if (!empty($value)) {
        return true;
      }
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
      new Specification('function', 'String', E::ts('Condition'), true, 'all are not empty', null, array(
        'all are not empty' => E::ts('All are not empty'),
        'one of is not empty' => E::ts('One of is not empty'),
        'all are empty' => E::ts('All are empty'),
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
