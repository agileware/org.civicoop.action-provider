<?php

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class FindByCustomGroupMultiValueId extends AbstractAction {

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
    $customGroup = civicrm_api4('CustomGroup', 'get', [
      'select' => ['name'],
      'where' => [
        ['id', '=', $this->configuration->getParameter('custom_group')],
      ],
      'checkPermissions' => $this->configuration->getParameter('check_permission'),
    ]);
    $customGroupName = $customGroup[0]['name'];

    if (empty($customGroupName)) {
      throw new InvalidParameterException(E::ts("No custom group provided."));
    }

    $entry_id = $parameters->getParameter('entry_id');
    if (empty($entry_id)) {
      throw new InvalidParameterException(E::ts("No entry id given."));
    }

    $apiParams = [
      'where' => [],
    ];
    $apiParams['where'][] = ['id', '=', $entry_id];
    $apiParams['where'][] = ['entity_id', '=', $parameters->getParameter('contact_id')];

    $apiParams['checkPermissions'] = $this->configuration->getParameter('check_permission');

    $apiCustomGroupName = 'Custom_' . $customGroupName;
    if (!count($apiParams)) {
      throw new InvalidParameterException(E::ts("No parameter given"));
    }

    $result = civicrm_api4($apiCustomGroupName, 'get', $apiParams);
    $output->setParameter('custom_group_id', $this->configuration->getParameter('custom_group'));
    if (isset($result[0])) {
      foreach ($result[0] as $key => $value) {
        if ($key === 'id') {
          $output->setParameter('custom_group_entry_id', $value);
        }
        else {
          if ($key === 'entity_id') {
            $output->setParameter($key, $value);
          }
          else {
            $fieldName = 'custom_' . $customGroupName . '_' . $key;
            $output->setParameter($fieldName, $value);
          }
        }
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
    return new SpecificationBag([
      new Specification('custom_group', 'Integer', E::ts('Custom group'), TRUE, NULL, 'CustomGroup', NULL, FALSE),
      new Specification('check_permission', 'Boolean', E::ts('Check permissions'), TRUE, NULL, NULL, NULL, FALSE),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE));
    $specs->addSpecification(new Specification('entry_id', 'Integer', E::ts('Custom group entry ID'), FALSE));

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
    $specs->addSpecification(new Specification('custom_group_entry_id', 'Integer', E::ts('Custom Group entry ID'), TRUE, NULL, NULL, NULL, FALSE));
    $specs->addSpecification(new Specification('custom_group_id', 'Integer', E::ts('Custom group ID'), TRUE, NULL, NULL, NULL, FALSE));
    $specs->addSpecification(new Specification('entity_id', 'Integer', E::ts('Entity ID'), TRUE, NULL, NULL, NULL, FALSE));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities([
      'Contact',
      'Individual',
      'Household',
      'Organization',
    ]);
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active']) && $customGroup['is_multiple']) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }

    return $specs;
  }

}
