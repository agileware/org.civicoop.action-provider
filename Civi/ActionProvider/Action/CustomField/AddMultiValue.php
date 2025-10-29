<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CustomField;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class AddMultiValue extends AbstractAction {

  protected $fields = [];

  public function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {  
    $entity_id = $parameters->getParameter('entity_id');
    $newValues = $parameters->getParameter('values');
    if (!is_array($newValues)) {
      if (is_scalar($newValues)) {
        $newValues[] = $newValues;
      } else {
        $newValues = [];
      }
    }
    $customFieldId = $this->configuration->getParameter('custom_field_id');

    $result = civicrm_api3('CustomValue', 'get', [
      'sequential' => 1,
      'return' => ["custom_" . $customFieldId],
      'entity_id' => $entity_id,
    ]);
    $values = [];
    if (isset($result['values'][0]['latest'])) {
      $values = $result['values'][0]['latest'];
    }
    $values = array_merge($values, $newValues);
    civicrm_api3('CustomValue', 'create', [
      'custom_' . $customFieldId => $values,
      'entity_id' => $entity_id,
    ]);
  }

  public function getParameterSpecification()
  {
    return new SpecificationBag([
      new Specification('entity_id', 'Integer', E::ts('Entity ID'), TRUE),
      new Specification('values', 'String', E::ts('Values to add'), false, null, null, null, true),
    ]);
  }

  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('custom_field_id', 'Integer', E::ts('Custom field'), true, null, 'CustomField'),
    ]);
  }

  public function getOutputSpecification()
  {
    return new SpecificationBag();
  }


}