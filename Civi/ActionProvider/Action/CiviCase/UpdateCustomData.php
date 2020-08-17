<?php

namespace Civi\ActionProvider\Action\CiviCase;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateCustomData extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $apiParams['id'] = $parameters->getParameter('case_id');
    $apiParams = array_merge($apiParams, CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification()));
    $result = civicrm_api3('Case', 'create', $apiParams);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
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
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('case_id', 'Integer', E::ts('Case ID'), true));
    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'is_active' => 1,
      'options' => ['limit' => 0],
    ]);
    foreach ($customGroups['values'] as $customGroup) {
      $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
    }
    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }


}
