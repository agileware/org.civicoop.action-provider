<?php

namespace Civi\ActionProvider\Action\Campaign;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateCampaign extends AbstractAction {

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
    $apiParams['id'] = $parameters->getParameter('campaign_id');
    foreach ([
               'title',
               'description',
               'start_date',
               'end_date',
               'is_active',
               'campaign_type_id',
               'status_id',
               'external_identifier',
               'parent_id',
               'goal_general',
               'goal_revenue'
             ] as $parameter) {
      if ($parameters->doesParameterExists($parameter)) {
        $apiParams[$parameter] = $parameters->getParameter($parameter);
      }
    }

    $apiParams = array_merge($apiParams, CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification()));
    $result = civicrm_api3('Campaign', 'create', $apiParams);
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
    $specs->addSpecification(new Specification('campaign_id', 'Integer', E::ts('Campaign ID'), true));
    $specs->addSpecification(new Specification('title', 'String', E::ts('Title'), false));
    $specs->addSpecification(new Specification('description', 'String', E::ts('Description'), false));
    $specs->addSpecification(new Specification('start_date', 'Date', E::ts('Start Date'), false));
    $specs->addSpecification(new Specification('end_date', 'Date', E::ts('End Date'), false));
    $specs->addSpecification(new Specification('is_active', 'Boolean', E::ts('Is Active'), false));
    $specs->addSpecification(new OptionGroupSpecification('campaign_type_id', 'campaign_type', E::ts('Campaign Type'), false));
    $specs->addSpecification(new OptionGroupSpecification('status_id', 'campaign_status', E::ts('Campaign Status'), false));
    $specs->addSpecification(new Specification('external_identifier', 'String', E::ts('External Identifier'), false));
    $specs->addSpecification(new Specification('parent_id', 'Integer', E::ts('Parent ID'), false));
    $specs->addSpecification(new Specification('goal_general', 'String', E::ts('Goal General'), false));
    $specs->addSpecification(new Specification('goal_revenue', 'Integer', E::ts('Goal Revenue'), false));

    $config = \Civi\ActionProvider\ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Campaign');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }

}
