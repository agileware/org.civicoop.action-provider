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

namespace Civi\ActionProvider;

use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\SpecificationBag;

class MetaData {

  /** @var \Civi\ActionProvider\Parameter\SpecificationBag  */
  protected $specification;

  /** @var \Civi\ActionProvider\Parameter\ParameterBag  */
  protected $metaData;

  public function __construct() {
    $this->specification = new SpecificationBag();
    $this->metaData = new ParameterBag();
  }

  public function getSpecificationBag(): SpecificationBag {
    return $this->specification;
  }

  public function getMetaData(): ParameterBag {
    return $this->metaData;
  }

}
