<?php

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class GenerateEmptyValues extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameter options for the actual
   * action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the output parameters for this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('emptyValueList', 'String', E::ts('Empty Value - List')),
      new Specification('emptyValueNull', 'String', E::ts('Empty Value - Null')),
      new Specification('emptyValueString', 'String', E::ts('Empty Value - String')),
    ]);
  }

  /**
   * Run the action.
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $output->setParameter('emptyValueList', []);
    $output->setParameter('emptyValueNull', 'null');
    $output->setParameter('emptyValueString', '');
  }

}
