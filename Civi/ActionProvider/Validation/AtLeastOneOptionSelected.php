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

class AtLeastOneOptionSelected extends AbstractValidator {

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $invalidValues = new Specification('invalid_values', 'String', E::ts('Invalid values'), true, '0');
    $invalidValues->setDescription(E::ts('Provide invalid values and seperate them by comma. E.g. 0,-1'));
    return new SpecificationBag([
      new Specification('message', 'String', E::ts('Invalid message'), true),
      $invalidValues,
    ]);
  }

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText():? string {
    return E::ts('When all the parameters contains one of the provided values the field is invalid.  E.g. I have a choicce between coming to the monday yes/no and a choice for tuesday training or sprint or not attending the sprint. The user has to either select going to monday or tuesday or both.');
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('parameters', 'String', E::ts('Parameter'), false, null, false, false, true),
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
    $values = explode(',', $this->configuration->getParameter('invalid_values'));
    $validCount = 0;
    $count = 0;
    foreach($parameters->getParameter('parameters') as $value) {
      $count ++;
      if ($value !== null && !in_array($value, $values)) {
        $validCount++;
      }
    }
    if ($count > 0 && $validCount == 0) {
      return $this->configuration->getParameter('message');
    }
    return null;
  }


}
