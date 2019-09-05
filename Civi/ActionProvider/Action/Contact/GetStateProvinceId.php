<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetStateProvinceId extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    if ($this->configuration->doesParameterExists('use_abbreviation') && $this->configuration->getParameter('use_abbreviation')) {
      $sql = "SELECT id FROM `civicrm_state_province` WHERE `country_id` = %1 AND `abbreviation` = %2";
      $params[1] = [$parameters->getParameter('country_id'), 'Integer'];
      $params[2] = [$parameters->getParameter('state_province'), 'String'];
    } else {
      $sql = "SELECT id FROM `civicrm_state_province` WHERE `country_id` = %1 AND `name` = %2";
      $params[1] = [$parameters->getParameter('country_id'), 'Integer'];
      $params[2] = [$parameters->getParameter('state_province'), 'String'];
    }
    $state_id = \CRM_Core_DAO::singleValueQuery($sql, $params);
    $output->setParameter('state_province_id', $state_id);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('use_abbreviation', 'Boolean', E::ts('Use abbreviation'), true, false),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('country_id', 'Integer', E::ts('Country ID'), true),
      new Specification('state_province', 'String', E::ts('State/Province name'), true),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag([
      new Specification('state_province_id', 'Integer', E::ts('State/Province ID'), true),
    ]);
    return $bag;
  }

}
