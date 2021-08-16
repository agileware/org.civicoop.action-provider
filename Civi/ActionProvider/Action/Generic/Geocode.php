<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
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

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class Geocode extends AbstractAction {

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
    $addressParams = array();
    if ($parameters->doesParameterExists('name')) {
      $addressParams['name'] = $parameters->getParameter('name');
    }
    if ($parameters->doesParameterExists('street_address')) {
      $addressParams['street_address'] = $parameters->getParameter('street_address');
    }
    if ($parameters->doesParameterExists('supplemental_address_1')) {
      $addressParams['supplemental_address_1'] = $parameters->getParameter('supplemental_address_1');
    }
    if ($parameters->doesParameterExists('street_name')) {
      $addressParams['street_name'] = $parameters->getParameter('street_name');
    }
    if ($parameters->doesParameterExists('street_number')) {
      $addressParams['street_number'] = $parameters->getParameter('street_number');
    }
    if ($parameters->doesParameterExists('street_unit')) {
      $addressParams['street_unit'] = $parameters->getParameter('street_unit');
    }
    if ($parameters->doesParameterExists('postal_code')) {
      $addressParams['postal_code'] = $parameters->getParameter('postal_code');
    }
    if ($parameters->doesParameterExists('city')) {
      $addressParams['city'] = $parameters->getParameter('city');
    }
    if ($parameters->doesParameterExists('state_province_id')) {
      $addressParams['state_province_id'] = $parameters->getParameter('state_province_id');
      $addressParams['state_province'] = \CRM_Core_PseudoConstant::stateProvinceAbbreviation($parameters->getParameter('state_province_id'));
    }
    if ($parameters->doesParameterExists('country_id')) {
      $addressParams['country_id'] = $parameters->getParameter('country_id');
      $addressParams['country'] = \CRM_Core_PseudoConstant::country($parameters->getParameter('country_id'));
    }
    try {
      $provider = \CRM_Utils_GeocodeProvider::getConfiguredProvider();
      $providerExists = TRUE;
    }
    catch (CRM_Core_Exception $e) {
      $providerExists = FALSE;
    }
    if ($providerExists && count($addressParams)) {
      $provider::format($addressParams);
      if ($addressParams['geo_code_1'] && $addressParams['geo_code_1'] !== 'null') {
        $latitude = (float) substr($addressParams['geo_code_1'], 0, 14);
        $output->setParameter('latitude', $latitude);
      }
      if ($addressParams['geo_code_2'] && $addressParams['geo_code_2'] !== 'null') {
        $longitude = (float) substr($addressParams['geo_code_2'], 0, 14);
        $output->setParameter('longitude', $longitude);
      }
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $spec = new SpecificationBag();
    $spec->addSpecification(new Specification('name', 'String', E::ts('Address name'), false));
    $spec->addSpecification(new Specification('street_address', 'String', E::ts('Street and housenumber'), false));
    $spec->addSpecification(new Specification('supplemental_address_1', 'String', E::ts('Supplemental address 1'), false));
    $spec->addSpecification(new Specification('street_name', 'String', E::ts('Street name'), false));
    $spec->addSpecification(new Specification('street_number', 'String', E::ts('Housenumber'), false));
    $spec->addSpecification(new Specification('street_unit', 'String', E::ts('Street Unit'), false));
    $spec->addSpecification(new Specification('postal_code', 'String', E::ts('Postal code'), false));
    $spec->addSpecification(new Specification('city', 'String', E::ts('City'), false));
    $spec->addSpecification(new Specification('state_province_id', 'Integer', E::ts('State/Province ID'), false, null, 'StateProvince'));
    $spec->addSpecification(new Specification('country_id', 'Integer', E::ts('Country ID'), false, null, 'Country'));
    return $spec;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('latitude', 'Float', E::ts('Latitude'), true),
      new Specification('longitude', 'Float', E::ts('Longitude'), true),
    ]);
  }


}
