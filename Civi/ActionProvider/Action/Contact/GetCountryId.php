<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetCountryId extends AbstractAction {

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
    if ($this->configuration->doesParameterExists('use_iso_code') && $this->configuration->getParameter('use_iso_code')) {
      $sql = "SELECT id FROM `civicrm_country` WHERE `iso_code` = %1";
      $params[1] = [$parameters->getParameter('country'), 'String'];
    }
    else {
      $sql = "SELECT id FROM `civicrm_country` WHERE `name` = %1";
      $params[1] = [$parameters->getParameter('country'), 'String'];
    }
    $country_id = \CRM_Core_DAO::singleValueQuery($sql, $params);
    $output->setParameter('country_id', $country_id);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('use_iso_code', 'Boolean', E::ts('Use Country ISO Code'), true, false),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('country', 'String', E::ts('State/Province name'), true),
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
      new Specification('country_id', 'Integer', E::ts('Country ID'), true),
    ]);
    return $bag;
  }

}
