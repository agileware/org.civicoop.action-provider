<?php
/**
 * Copyright (C) 2024  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Event;
use Civi\Api4\Participant;

abstract class AbstractParticipantAction extends AbstractAction {

  protected function needToRegisterOnWaitlist(int $eventId) {
    try {
      $event = Event::get(FALSE)
        ->addWhere('id', '=', $eventId)
        ->execute()
        ->first();
    if (isset($event['max_participants']) && !empty($event['has_waitlist'])) {
      $participantCount = Participant::get(FALSE)
        ->addWhere('event_id', '=', $eventId)
        ->addWhere('status_id.is_counted', '=', TRUE)
        ->execute()
        ->count();
      $availablePlaces = $event['max_participants'] - $participantCount;
      return $availablePlaces <= 0;
    }
    }
    catch (UnauthorizedException|\CRM_Core_Exception $e) {
      // Do nothing.
    }
    return false;
  }

}
