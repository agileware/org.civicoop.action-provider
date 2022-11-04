<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Group;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetGroup extends AbstractAction {

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
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Group ID'), true),
    ]);
    return $bag;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    $bag->addSpecification(new Specification('id', 'Integer', E::ts('Group ID')));
    $bag->addSpecification(new Specification('title', 'String', E::ts('Title')));
    $bag->addSpecification(new Specification('description', 'Text', E::ts('Description')));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Group');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $bag->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }

    return $bag;
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $groupParams['id'] = $parameters->getParameter('id');
    try {
      // Do not use api as the api checks for an existing relationship.
      $group = civicrm_api3('Group', 'getsingle', $groupParams);
      foreach($group as $field => $value) {
        if (stripos($field, 'custom_') !== 0) {
          $output->setParameter($field, $value);
        } else {
          $custom_id = substr($field, 7);
          $fieldName = CustomField::getCustomFieldName($custom_id);
          $output->setParameter($fieldName, $value);
        }
      }
    } catch (\Exception $e) {
      // Do nothing.
    }
  }



}
