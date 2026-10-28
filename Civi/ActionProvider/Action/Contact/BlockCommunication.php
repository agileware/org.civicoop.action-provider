<?php
/**
 * Copyright (C) 2022  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class BlockCommunication extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    try {
      $cid = $parameters->getParameter('contact_id');
      $params['id'] = $cid;
      $which = $this->configuration->getParameter('which');
      if (!is_array($which)) {
        $which = ['all'];
      }
      if (in_array('all', $which)) {
        $params['is_opt_out'] = '1';
        $params['do_not_email'] = '1';
        $params['do_not_phone'] = '1';
        $params['do_not_mail'] = '1';
        $params['do_not_sms'] = '1';
        $params['do_not_trade'] = '1';
      } else {
        foreach($which as $k) {
          $params[$k] = '1';
        }
      }
      
      civicrm_api3('Contact', 'create', $params);
    } catch (\CRM_Core_Exception $ex) {
      // Do nothing.
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $options = [
      'all' => E::ts('Block all communication'),
      'is_opt_out' => E::ts('No Bulk Emails (User opt out)'),
      'do_not_email' => E::ts('Do not e-mail'),
      'do_not_phone' => E::ts('Do not phone'),
      'do_not_mail' => E::ts('Do not mail'),
      'do_not_sms' => E::ts('Do not sms'),
      'do_not_trade' => E::ts('Do not trade'),
    ];
    return new SpecificationBag([
      new Specification('which', 'String', E::ts('Which communication to block?'), TRUE, 'all', NULL, $options, TRUE),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE),
    ]);
  }


}
