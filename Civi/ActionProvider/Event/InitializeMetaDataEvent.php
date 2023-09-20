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
namespace Civi\ActionProvider\Event;

use Civi\Core\Event\GenericHookEvent;
use Civi\ActionProvider\MetaData;

class InitializeMetaDataEvent extends GenericHookEvent {

  const EVENT_NAME = 'ActionProviderInitializeMetaDataEvent';

  /** @var \Civi\ActionProvider\MetaData  */
  private $metaData;

  /**
   * @param \Civi\ActionProvider\MetaData $metaData
   */
  public function __construct(MetaData $metaData) {
    $this->metaData = $metaData;
  }

  /**
   * @return \Civi\ActionProvider\MetaData
   */
  public function getMetaData(): MetaData {
    return $this->metaData;
  }

}
