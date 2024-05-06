<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class FormatDateValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $format = new Specification('format', 'String', E::ts('Date format'), true);
    $format->setDescription('Format the date value, see <a href="https://secure.php.net/manual/en/datetime.format.php">PHP date_format</a>.');

    return new SpecificationBag(array($format));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag([
      // Currently, have to use String as the data type because Date has no validator implementation and will throw an invalid parameter exception
      new Specification('value', 'String', E::ts('Date'), true),
    ]);
  }

  /**
   * Validates parameters.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return bool
   */
  public function validateParameters(ParameterBagInterface $parameters) {
    // Check a valid date has been provided - why does Action Provider have no built-in validation for Date?
    if (!\DateTime::createFromFormat('Y-m-d',$parameters->getParameter('value'))) {
      return false;
    }

    return parent::validateParameters($parameters);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('value', 'String', E::ts('Value')),
    ]);
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
    try {
      $date           = new \DateTime( $parameters->getParameter( 'value' ) );
      $value = $date->format($this->configuration->getParameter('format'));
      $output->setParameter('value', $value);
    } catch (\Exception) {
      return FALSE;
    }
  }

}
