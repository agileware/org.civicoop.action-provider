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

class CalculateValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification(
        'operator',
        'String',
        E::ts('Operator'),
        true,
        null,
        null,
        static::validOperators(false)
      ),
      new Specification(
        'operand1',
        'String',
        E::ts('First operand')
      ),
      new Specification(
        'operand2',
        'String',
        E::ts('Second operand')
      ),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification(
        'operand1',
        'String',
        E::ts('First operand')
      ),
      new Specification(
        'operand2',
        'String',
        E::ts('Second operand')
      ),
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
    return new SpecificationBag(array(
      new Specification('value', 'String', E::ts('Value')),
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
    if ($configuration->getParameter('operand1')) {
      $operand1 = $configuration->getParameter('operand1');
    }
    // Overwrite with first operand from parameters if given.
    if ($parameters->doesParameterExists('operand1')) {
      $operand1 = $parameters->getParameter('operand1');
    }

    // Get default first operand from configuration.
    if ($configuration->getParameter('operand2')) {
      $operand2 = $configuration->getParameter('operand2');
    }
    // Overwrite with first operand from parameters if given.
    if ($parameters->doesParameterExists('operand2')) {
      $operand2 = $parameters->getParameter('operand2');
    }

    // Check for existing operands.
    if (empty($operand1)) {
      throw new InvalidParameterException('First operand is required.');
    }
    if (empty($operand2)) {
      throw new InvalidParameterException('Second operand is required.');
    }

    // Check for data types.
    if (!is_numeric($operand1)) {
      throw new InvalidParameterException('First operand must be numeric.');
    }
    if (!is_numeric($operand2)) {
      throw new InvalidParameterException('Second operand must be numeric.');
    }

    // Check for valid operator.
    if (!in_array($configuration->getParameter('operator'), static::validOperators())) {
      throw new InvalidParameterException('Invalid operator.');
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
    // Get default first operand from configuration.
    if ($this->configuration->getParameter('operand1')) {
      $operand1 = $this->configuration->getParameter('operand1');
    }
    // Overwrite with first operand from parameters if given.
    if ($parameters->doesParameterExists('operand1')) {
      $operand1 = $parameters->getParameter('operand1');
    }

    // Get default first operand from configuration.
    if ($this->configuration->getParameter('operand2')) {
      $operand2 = $this->configuration->getParameter('operand2');
    }
    // Overwrite with first operand from parameters if given.
    if ($parameters->doesParameterExists('operand2')) {
      $operand2 = $parameters->getParameter('operand2');
    }

    $operator = $this->configuration->getParameter('operator');

    $formula = $operand1 . $operator . $operand2;
    $result = eval('return ' . $formula . ';');

    $output->setParameter('value', $result);
  }

  /**
   * Returns valid operators for this action.
   *
   * @param bool $keys
   *   Whether to only return keys, i.e. the valid operators in PHP syntax.
   *
   * @return array
   *   An array of valid operators. If $keys is FALSE, their PHP syntax
   *   is used as keys, and a visual expression is used as values.
   */
  public static function validOperators($keys = TRUE) {
    $operators = array(
      '+' => '+ (Sum)',
      '-' => '- (Difference)',
      '*' => '* (Product)',
      '/' => '/ (Quotient)',
      '%' => '% (Remainder)',
      '**' => '** (Power of)',
    );

    if ($keys) {
      $operators = array_keys($operators);
    }

    return $operators;
  }

}
