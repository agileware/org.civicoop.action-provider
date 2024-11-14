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
namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Membership;
use CRM_ActionProvider_ExtensionUtil as E;

class UnlinkContributionRecurFromMembership extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @throws \Civi\ActionProvider\Exception\ExecutionException
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    try {
      Membership::update(FALSE)
        ->addValue('contribution_recur_id', null)
        ->addWhere('id', '=', $parameters->getParameter('membership_id'))
        ->execute();
    }
    catch (UnauthorizedException|\CRM_Core_Exception $e) {
      $tsParams = [
        1 => $e->getMessage(),
        2 => $parameters->getParameter('membership_id'),
      ];
      throw new ExecutionException(E::ts('Could not remove contribution recur from membership (%2). Because of: %1', $tsParams), $e->getCode(), $e);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('membership_id', 'Integer', E::ts('Membership ID'), true),
    ));
  }


}
