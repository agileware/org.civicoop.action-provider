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

class CompareParameterRegex extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $parameter = $parameterBag->getParameter('parameter');
    $regex = $this->configuration->getParameter('regex');
    $inverse = $this->configuration->getParameter('inverse');
    if (preg_match($regex,$parameter)) {
      if($inverse){
        return false;
      }
      return true;
    }
    if($inverse){
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
    return new SpecificationBag(array(
      new Specification('regex', 'String', E::ts('Regular Expression'), true),
      new Specification('inverse', 'String', E::ts('Invert Regex Matching Result'), false, null, null, $this->getInvertOptions()),
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
    return E::ts('Compare Parameter with an regular Expression');
  }

  protected function getInvertOptions() {
    return [
            0  => E::ts("no"),
            1  => E::ts("yes"),
        ];
  }
}