<?php
/**
 * Copyright (C) 2025  Jaap Jansma (jaap.jansma@civicoop.org)
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

class IsEmailAddressValid extends AbstractValidator {

  const PATTERN_LOOSE = '/^.+\@\S+\.\S+$/';
  public function getConfigurationSpecification(): SpecificationBag
  {
    return new SpecificationBag([
      new Specification('message', 'String', E::ts('Invalid message'), true),
    ]);
  }

  public function getParameterSpecification(): SpecificationBag
  {
    return new SpecificationBag([
      new Specification('email', 'String', E::ts('E-mail address'), false),
    ]);
  }

  protected function doValidation(ParameterBagInterface $parameters): ?string
  {
    $email = $parameters->getParameter('email');
    if (!empty($email) && !preg_match(static::PATTERN_LOOSE, $email)) {
      return $this->configuration->getParameter('message');
    }
    return null;
  }


}