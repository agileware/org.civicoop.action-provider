<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class SetDateValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $date = new Specification('date', 'String', E::ts('Date'), true);
    $date->setDescription('Set the date value follow the formats from <a href="https://secure.php.net/manual/en/datetime.formats.php">PHP.net date time</a>.');
    return new SpecificationBag(array(
      $date,
      new Specification('include_time', 'Boolean', E::ts('Include time'), true, '0', '', array(
        '0' => E::ts('No'),
        '1' => E::ts('Yes')
      )),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('date', 'Date', E::ts('Date')),
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
    $date = new \DateTime($this->configuration->getParameter('date'));
    if ($this->configuration->doesParameterExists('include_time') && $this->configuration->getParameter('include_time')) {
      $output->setParameter('date', $date->format('YmdHis'));
    } else {
      $output->setParameter('date', $date->format('Ymd'));
    }
  }

}