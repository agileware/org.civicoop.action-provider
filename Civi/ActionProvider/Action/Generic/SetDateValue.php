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
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Set date value');
  }

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
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag();
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
    $output->setParameter('date', $date->format('Ymd'));
  }

  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::DATA_MANIPULATION_TAG
    );
  }

}