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

class ParameterIsEmpty extends AbstractValidator {

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    return new SpecificationBag([
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
    if ($parameters->doesParameterExists('parameter') && strlen((string) $parameters->getParameter('parameter')) == 0) {
      return $this->configuration->getParameter('message');
    }
    return null;
  }


}
