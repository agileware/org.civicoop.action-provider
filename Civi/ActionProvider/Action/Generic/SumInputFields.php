<?php
/**
 * @author Jens Schuppe <schuppe@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class SumInputFields extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification(
        'offsetValue',
        'Float',
        E::ts('Offset value')
      ), ));
  }


  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    for ($id = 1; $id < 7; $id++) {
      $specs->addSpecification(new Specification("input_{$id}", 'Float', E::ts("{$id}. input")));
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
      new Specification('value', 'Float', E::ts('Sum of inputs.')),
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

    $result = 0.;
    for ($id = 1; $id < 7; $id++) {
      if ($parameters->doesParameterExists("input_{$id}")) {
        $result += floatval($parameters->getParameter("input_{$id}"));
      }
    }
    if ($this->configuration->doesParameterExists('offsetValue')) {
      $result += $this->configuration->getParameter('offsetValue');
    }
    $output->setParameter('value', $result);
  }

}