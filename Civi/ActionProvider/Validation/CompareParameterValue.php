<?php
/**
 * Copyright (C) 2023  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Civi\ActionProvider\Validation;

use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class CompareParameterValue extends AbstractValidator {

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $operators = [
      '=' => E::ts('Parameter is equal'),
      '!=' => E::ts('Parameter is not equal'),
      '>' => E::ts('Parameter is greater than'),
      '>=' => E::ts('Parameter is greater than or equal'),
      '<' => E::ts('Parameter is less than'),
      '<=' => E::ts('Parameter is less than or equal'),
    ];
    return new SpecificationBag([
      new Specification('value', 'String', E::ts('Value'), true),
      new Specification('operator', 'String', E::ts('Comparison'), true, '=', null, $operators),
      new Specification('message', 'String', E::ts('Invalid message'), true),
    ]);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('parameter', 'String', E::ts('Parameter'), false),
    ]);
  }

  /**
   * Returns null when valid. When invalid return a string containing an explanation message.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return string|null
   */
  protected function doValidation(ParameterBagInterface $parameters):? string {
    $isValid = true;
    if ($parameters->doesParameterExists('parameter')) {
      $value = $parameters->getParameter('parameter');
      $comparisonValue = $this->configuration->getParameter('value');
      $operator = $this->configuration->getParameter('operator');
      switch ($operator) {
        case '=':
          $isValid = $value == $comparisonValue ? true : false;
          break;
        case '!=':
          $isValid = $value != $comparisonValue ? true : false;
          break;
        case '>':
          $isValid = $value > $comparisonValue ? true : false;
          break;
        case '>=':
          $isValid = $value >= $comparisonValue ? true : false;
          break;
        case '<':
          $isValid = $value < $comparisonValue ? true : false;
          break;
        case '<=':
          $isValid = $value <= $comparisonValue ? true : false;
          break;
        default:
          $isValid = false;
      }
    }

    if (!$isValid) {
      return $this->configuration->getParameter('message');
    }
    return null;
  }


}
