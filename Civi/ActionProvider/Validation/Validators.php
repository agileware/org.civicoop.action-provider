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

use Civi\ActionProvider\Provider;
use CRM_ActionProvider_ExtensionUtil as E;

class Validators {

  /**
   * @param \Civi\ActionProvider\Provider $provider
   *
   * @return void
   */
  public static function loadValidators(Provider $provider) {
    $provider->addValidator('is_not_empty', 'Civi\ActionProvider\Validation\ParameterIsNotEmpty', E::ts('Parameter is not empty'));
    $provider->addValidator('is_empty', 'Civi\ActionProvider\Validation\ParameterIsEmpty', E::ts('Parameter is empty'));
    $provider->addValidator('compare_parameter_value', 'Civi\ActionProvider\Validation\CompareParameterValue', E::ts('Compare Parameter Value'));
    $provider->addValidator('has_role_on_case', 'Civi\ActionProvider\Validation\HasContactRoleOnCase', E::ts('CiviCase: Contact has role on case'));
  }

}
