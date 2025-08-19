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

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\SpecificationGroup;
use CRM_ActionProvider_ExtensionUtil as E;

class EventActionUtils {

  public static function getLineItemInfo(ParameterBag $parameters): array {
    $priceOptions = [];
    if ($parameters->doesParameterExists('price_set_field_values')) {
      $priceOptions = $parameters->getParameter('price_set_field_values');
    }
    if ($parameters->doesParameterExists('price_field_value_id')) {
      $priceOptions[] = $parameters->getParameter('price_field_value_id');
    }
    $qty = $parameters->getParameter('qty');
    if ($qty < 1) {
      $qty = 1;
    }

    $selectedPriceOptions = [];
    foreach ($priceOptions as $priceOption) {
      if ($priceOption) {
        $selectedPriceOptions[] = [
          'id' => $priceOption,
          'qty' => $qty,
        ];
      }
    }
    $taxRates = self::getTaxRates();
    $priceSets = self::getPriceSetMetaData();
    foreach ($priceSets as $priceSet) {
      foreach ($priceSet['fields'] as $field) {
        if ($parameters->doesParameterExists('pricefield_'.$field['id']) && $parameters->getParameter('pricefield_'.$field['id'])) {
          if ($field['is_enter_qty']) {
            $option = reset($field['options']);
            $selectedPriceOptions[] = [
              'id' => $option['id'],
              'qty' => $parameters->getParameter('pricefield_'.$field['id']),
            ];
          } else {
            $selectedPriceOptions[] = [
              'id' => $parameters->getParameter('pricefield_'.$field['id']),
              'qty' => 1,
            ];
          }
        }
      }
    }
    foreach ($selectedPriceOptions as $index => $selectedPriceOption) {
      $priceFieldValue = \Civi\Api4\PriceFieldValue::get(FALSE)
        ->addSelect('*', 'price_field_id.id')
        ->addWhere('id', '=', $selectedPriceOption['id'])
        ->execute()
        ->first();

      $amount = $priceFieldValue['amount'];
      $selectedPriceOptions[$index]['tax_rate'] = (float) ($taxRates[$priceFieldValue['financial_type_id']] ?? 0);
      $selectedPriceOptions[$index]['tax_amount'] = 0;
      if ($selectedPriceOptions[$index]['tax_rate']) {
        $tax_amount = \CRM_Contribute_BAO_Contribution_Utils::calculateTaxAmount($priceFieldValue['amount'], $selectedPriceOptions[$index]['tax_rate'])['tax_amount'];
        $selectedPriceOptions[$index]['tax_amount'] = $tax_amount;
      }
      $selectedPriceOptions[$index]['amount'] = $amount;
      $selectedPriceOptions[$index]['field_value'] = $priceFieldValue;
    }
    return $selectedPriceOptions;
  }

  public static function addPriceFieldsToSpecification(SpecificationBag $specs): SpecificationBag {
    $priceSets = static::getPriceSetMetaData();
    foreach ($priceSets as $priceSet) {
      $priceSetSpec = new SpecificationBag();
      foreach ($priceSet['fields'] as $field) {
        if (!empty($field['is_enter_qty'])) {
          $priceSetFielSpec = new Specification('pricefield_'.$field['id'], 'Integer', E::ts('Quantity of %1', [1=>$field['label']]));
        } else {
          $priceSetFielSpec = new Specification('pricefield_'.$field['id'], 'Integer', E::ts('Selected option of %1', [1=>$field['label']]));
        }
        $priceSetFielSpec->setApiFieldName($field['name']);
        $priceSetSpec->addSpecification($priceSetFielSpec);
      }
      $specs->addSpecification(new SpecificationGroup($priceSet['name'], $priceSet['title'], $priceSetSpec));
    }
    return $specs;
  }

  public static function getTaxRates(): array {
    static $taxRates;
    $key = E::SHORT_NAME . '.tax_rates';
    if (empty($metaData)) {
      $taxRates = \Civi::cache('long')->get($key, NULL);
      if (empty($taxRates)) {
        $taxRates = \CRM_Core_PseudoConstant::getTaxRates();
        \Civi::cache('long')->set($key, $taxRates, 300);
      }
    }
    return is_array($taxRates) ? $taxRates : [];
  }

  public static function getPriceSetMetaData(): array {
    static $metaData = [];
    $key = E::SHORT_NAME . '.priceSetMetaData';
    if (empty($metaData)) {
      $metaData = \Civi::cache('long')->get($key, []);
      if (empty($metaData)) {
        try {
          $priceSets = \Civi\Api4\PriceSet::get(FALSE)
            ->addWhere('extends', 'CONTAINS', 1)
            ->addWhere('is_active', '=', TRUE)
            ->execute();
          foreach ($priceSets as $priceSet) {
            $metaData[$priceSet['id']] = \CRM_Price_BAO_PriceSet::getCachedPriceSetDetail($priceSet['id']);
          }
          \Civi::cache('long')->set($key, $metaData, 300);
        } catch (\Throwable $e) {
          // Do nothing.
        }
      }
    }
    return $metaData;
  }

}