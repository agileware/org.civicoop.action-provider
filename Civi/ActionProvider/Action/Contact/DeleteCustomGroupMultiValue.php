<?php

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class DeleteCustomGroupMultiValue extends AbstractAction {

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
    $customGroupName = '';
    if ($this->configuration->getParameter('custom_group')) {
      $customGroup = civicrm_api4('CustomGroup', 'get', [
        'select' => ['name'],
        'where' => [
          ['id', '=', $this->configuration->getParameter('custom_group')],
        ],
        'checkPermissions' => $this->configuration->getParameter('check_permission'),
      ]);
      $customGroupName = $customGroup[0]['name'];
    }
    else {
      throw new InvalidParameterException(E::ts("No custom group provided."));
    }

    $apiCustomGroupName = 'Custom_' . $customGroupName;

    if (!$parameters->doesParameterExists('entry_id')) {
      throw new InvalidParameterException(E::ts("No entry ID provided."));
    }

    $apiParams['where'] = [];
    $apiParams['where'][] = ['id', '=', $parameters->getParameter('entry_id')];

    $contact_id = $parameters->getParameter('contact_id');
    if (!empty($contact_id)) {
      $apiParams['where'][] = ['entity_id', '=', $contact_id];
    }

    $apiParams['checkPermissions'] = $this->configuration->getParameter('check_permission');

    try {
      $result = civicrm_api4($apiCustomGroupName, 'delete', $apiParams);
    }
    catch (\CiviCRM_API3_Exception $ex) {
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
        new Specification('custom_group', 'String', E::ts('Custom group'), FALSE, NULL, 'CustomGroup', NULL, FALSE),
        new Specification('check_permission', 'Boolean', E::ts('Check permissions'), TRUE, NULL, NULL, NULL, FALSE),
      ]
    );
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE));
    $specs->addSpecification(new Specification('entry_id', 'Integer', E::ts('Custom group entry ID'), TRUE));

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
    return new SpecificationBag([
      new Specification('test_output', 'String', E::ts('Output for testing purposes'), TRUE, NULL, NULL, NULL, FALSE),
    ]);
  }

}
