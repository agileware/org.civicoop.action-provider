<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateCustomGroupMultiValue extends AbstractAction {

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

    $customGroupName = '';
    if ($this->configuration->getParameter('custom_group')) {
      $customGroup = civicrm_api4('CustomGroup', 'get', array(
        'select' => array('name'),
        'where' => array(
          array('id', '=', $this->configuration->getParameter('custom_group')),
        ),
        'checkPermissions' => $this->configuration->getParameter('check_permission'),
      ));
      $customGroupName = $customGroup[0]['name'];
    }
    else if ($parameters->doesParameterExists('custom_group')) {
      $customGroup = civicrm_api4('CustomGroup', 'get', array(
        'select' => array('name'),
        'where' => array(
          array('id', '=', $parameters->getParameter('custom_group')),
        ),
        'checkPermissions' => $this->configuration->getParameter('check_permission'),
      ));
      $customGroupName = $customGroup[0]['name'];
    }
    else {
      throw new InvalidParameterException(E::ts("No custom group provided."));
    }

    $updateEntry = $parameters->doesParameterExists('entry_id');

    $apiParams = array(
      'values' => array(),
    );
    if ($updateEntry) {
      $apiParams['where'] = array();
    }
    foreach ($this->getParameterSpecification() as $spec) {
      if ($spec->getName() === 'contact_id') {
        if ($updateEntry) {
          array_push($apiParams['where'], array('entity_id', '=', $parameters->getParameter('contact_id')));
        }
        else {
          $apiParams['values']['entity_id'] = $parameters->getParameter($spec->getName());
        }
      }
      else if ($spec->getName() == 'entry_id' and $updateEntry) {
        array_push($apiParams['where'], array('id', '=', $parameters->getParameter('entry_id')));
      }
      else if ($spec->getName() === $customGroupName) {
        foreach ($spec->getSpecificationBag() as $subspec) {
          if ($parameters->doesParameterExists($subspec->getName())) {
            $apiSpecName = str_replace('custom_' . $spec->getName() . '_', '', $subspec->getName());
            $apiParams['values'][$apiSpecName] = $parameters->getParameter($subspec->getName());
          }
        }

      }
    }
    $apiParams['checkPermissions'] = $this->configuration->getParameter('check_permission');

    $apiCustomGroupName = 'Custom_' . $customGroupName;


    if (!count($apiParams)) {
      throw new InvalidParameterException(E::ts("No parameter given"));
    }
    try {
      if ($updateEntry) {
        $entry_id = civicrm_api4($apiCustomGroupName, 'update', $apiParams);
      }
      else {
        $entry_id = civicrm_api4($apiCustomGroupName, 'create', $apiParams);
      }
    }
    catch (\CiviCRM_API3_Exception $ex) {

    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('custom_group', 'String', E::ts('Custom group'), false, null, 'CustomGroup', null, FALSE),
      new Specification('check_permission', 'Boolean', E::ts('Check permissions'), true, null, null, null, FALSE),
    )
    );
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), true));
    $specs->addSpecification(new Specification('custom_group', 'String', E::ts('CustomGroup ID'), false));
    $specs->addSpecification(new Specification('entry_id', 'Integer', E::ts('Custom group entry ID'), false));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities(['Contact', 'Individual', 'Household', 'Organization']);
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
    return new SpecificationBag(array(
      new Specification('test_output', 'String', E::ts('Output for testing puporses'), true, null, null, null, false),
    ));
  }


}