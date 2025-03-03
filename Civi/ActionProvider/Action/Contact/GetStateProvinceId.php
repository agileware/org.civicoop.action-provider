<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetStateProvinceId extends AbstractAction {

  const LOOKUP_TYPE_NAME = 0;
  const LOOKUP_TYPE_ABBREV = 1;
  const LOOKUP_TYPE_BOTH = 2;

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
    if ($this->configuration->doesParameterExists('use_abbreviation') && $this->configuration->getParameter('use_abbreviation') == $this::LOOKUP_TYPE_ABBREV) {
      $sql = "SELECT id FROM `civicrm_state_province` WHERE `country_id` = %1 AND `abbreviation` = %2";
    }
    elseif ($this->configuration->doesParameterExists('use_abbreviation') && $this->configuration->getParameter('use_abbreviation') == $this::LOOKUP_TYPE_NAME){
      $sql = "SELECT id FROM `civicrm_state_province` WHERE `country_id` = %1 AND `name` = %2";
    } else {
      $sql = "SELECT id FROM `civicrm_state_province` WHERE `country_id` = %1 AND `name` = %2";
      $sql .= " UNION SELECT id FROM `civicrm_state_province` WHERE `country_id` = %1 AND `abbreviation` = %2";
    }
    $params[1] = [$parameters->getParameter('country_id'), 'Integer'];
    $params[2] = [$parameters->getParameter('state_province'), 'String'];

    $state_id = \CRM_Core_DAO::singleValueQuery($sql, $params);
    $output->setParameter('state_province_id', $state_id);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $options = [
      $this::LOOKUP_TYPE_NAME => E::ts('Name'),
      $this::LOOKUP_TYPE_ABBREV => E::ts('Abbreviation'),
      $this::LOOKUP_TYPE_BOTH => E::ts('Both'),
    ];
    return new SpecificationBag(array(
      new Specification('use_abbreviation', 'Integer', E::ts('Use by name or abbreviation'), true, 0, NULL, $options),
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
   * This function could be overridden by child classes.
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
