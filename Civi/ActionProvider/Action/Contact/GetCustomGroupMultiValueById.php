<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetCustomGroupMultiValueById extends AbstractAction {

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

    $customGroup = civicrm_api4('CustomGroup', 'get', array(
      'select' => array('name'),
      'where' => array(
        array('id', '=', $parameters->getParameter('custom_group_id')),
      )
    ));
    $customGroupName = 'Custom_' . $customGroup[0]['name'];

    $result = civicrm_api4($customGroupName, 'get', array(
      'where' => array(
        array('id', '=', $parameters->getParameter('entry_id')),
      ),
    ));

    foreach ($result[0] as $key => $value) {
      if ($key === 'entity_id' || $key === 'id') {
        continue;
      }
      else {
        $output->setParameter($key, $value);
      }
    }
    $output->setParameter('apiCall', 'Test');

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
    $specs->addSpecification(new Specification('custom_group_id', 'Integer', E::ts('CustomGroup ID'), true));
    $specs->addSpecification(new Specification('entry_id', 'Integer', E::ts('Custom group entry ID'), true));

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
    $specs = new SpecificationBag();
    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities(['Contact', 'Individual', 'Household', 'Organization']);
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active']) && $customGroup['is_multiple']) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    $specs->addSpecification(new Specification('apiCall', 'String', E::ts('Result of the ApiCall')));
    return $specs;
  }


}