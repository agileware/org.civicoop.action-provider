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
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class Create extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $visibilityOptions = \CRM_Contact_DAO_Group::buildOptions('visibility');
    $checkTitle = new Specification('check_for_title', 'Boolean', E::ts('Check title for existence'), false, false);
    $checkTitle->setDescription(E::ts('Check whether the group already exists based on the title'));
    return new SpecificationBag([
      $checkTitle,
      new OptionGroupSpecification('group_type','group_type', E::ts('Group type'), FALSE),
      new Specification('visibility','String', E::ts('Visibility'), FALSE, null, null, $visibilityOptions),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Group ID'), FALSE),
      new Specification('title', 'String', E::ts('Group Title'), TRUE),
      new Specification('description', 'Text', E::ts('Description'), FALSE),
    ]);

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
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('id', 'Integer', E::ts('Group record ID')),
    ));
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
    // Get the contact and the event.
    if ($parameters->doesParameterExists('id')) {
      $groupApiParams['id'] = $parameters->getParameter('id');
    } elseif ($this->configuration->getParameter('check_for_title')) {
      try {
        $groupApiParams['id'] = civicrm_api3('Group', 'getvalue', [
          'return' => 'id',
          'is_active' => 1,
          'is_hidden' => 0,
          'title' => $parameters->getParameter('title')
        ]);
      } catch (\CiviCRM_API3_Exception $ex) {
        // Do nothing
      }
    }
    $groupApiParams['title'] = $parameters->getParameter('title');
    $groupApiParams['description'] = $parameters->getParameter('description');
    if ($this->configuration->doesParameterExists('group_type')) {
      $groupApiParams['group_type'] = $this->configuration->getParameter('group_type');
    }
    if ($this->configuration->doesParameterExists('visibility')) {
      $groupApiParams['visibility'] = $this->configuration->getParameter('visibility');
    }
    foreach($this->getParameterSpecification() as $spec) {
      if (stripos($spec->getName(), 'custom_')!==0) {
        continue;
      }
      if ($parameters->doesParameterExists($spec->getName())) {
        $groupApiParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
      }
    }

    try {
      // Do not use api as the api checks for an existing relationship.
      $result = civicrm_api3('Group', 'Create', $groupApiParams);
      $output->setParameter('id', $result['id']);
    } catch (\Exception $e) {
      // Do nothing.
    }
  }



}
