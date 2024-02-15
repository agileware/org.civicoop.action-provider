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

class ConcatenateValues extends AbstractAction {

  public function getSpecifications(){
    $specifications = [];
    for ($k = 0 ; $k < 5; $k++) {
      $specifications[$k] = new Specification(
        'text'.$k,
        'String',
        E::ts('Text'.$k)
      );
    }
    $specifications['seperator'] = new Specification(
      'separator',
      'String',
      E::ts('Separator (leave empty for space)')
    );
    return $specifications;
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag($this->getSpecifications());
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag($this->getSpecifications());
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
      new Specification('concatenation', 'String', E::ts('Concatenated strings')),
    ));
  }

  /**
   * Validates the input parameters.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return bool
   *
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  protected function validateParameters(ParameterBagInterface $parameters) {
    // Either configuration or parameters for operands must be present.
    $configuration = $this->getConfiguration();

    // Get default first operand from configuration.
    if ($configuration->getParameter('text0')) {
      $text0 = $configuration->getParameter('text0');
    }
    // Overwrite with first operand from parameters if given.
    if ($parameters->doesParameterExists('text0')) {
      $text0 = $parameters->getParameter('text0');
    }

    // Check for existing operands.
    if (is_null($text0)) {
      throw new InvalidParameterException('The first value is required.');
    }

    return parent::validateParameters($parameters);
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
    $i = 0;
    $values = [];
    /** @var Specification $specification */
    foreach ($this->getSpecifications() as $specification) {
      $name = $specification->getName();
      if ($name != "separator") {
        // Get default value from configuration.
        if ($this->configuration->getParameter($name)) {
          $values[$i] = $this->configuration->getParameter($name);
        }
        // Overwrite with value from parameters if given.
        if ($parameters->doesParameterExists($name)) {
          $values[$i] = $parameters->getParameter($name);
        }
      } else {
        // Get default value from configuration.
        if ($this->configuration->getParameter('separator')) {
          $separator = $this->configuration->getParameter($name);
        }
        // Overwrite with value from parameters if given.
        if ($parameters->doesParameterExists('separator')) {
          $separator = $parameters->getParameter($name);
        }
      }
      $i ++;
    }
    $s = $separator ?? " ";
    $result = implode($s,$values);

    $output->setParameter('concatenation', $result);
  }

}
