<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\ActionProvider\Parameter\SpecificationCollection;
use CRM_ActionProvider_ExtensionUtil as E;

class ParametersAreEqual extends AbstractCondition {

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  public function isConditionValid(ParameterBagInterface $parameterBag) {
    $parameters1 = $parameterBag->getParameter('parameters1');
    $parameters2 = $parameterBag->getParameter('parameters2');

    if (count($parameters1) != count($parameters2)) {
      return false;
    }
    foreach($parameters1 as $index => $parameter1) {
      $value1 = $parameter1->getParameter('parameter');
      $value2 = $parameters2[$index]->getParameter('parameter');
      if ($value1 !== $value2) {
        return false;
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
    return new SpecificationBag(array());
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
    $parameterCollection1 = new SpecificationCollection('parameters1', E::ts('Parameters (1)'), $parametersBag, 1);
    $parameterCollection2 = new SpecificationCollection('parameters2', E::ts('Parameters (2)'), $parametersBag, 0);
    return new SpecificationBag(array(
      $parameterCollection1,
      $parameterCollection2,
    ));
  }

  /**
   * Returns the human readable title of this condition
   */
  public function getTitle() {
    return E::ts('Parameters are equal');
  }

}
