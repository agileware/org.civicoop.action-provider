<?php
/**
 * @author Björn Endres <endres@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Class RegexReplaceValue
 * @package Civi\ActionProvider\Action\Generic
 *
 * Runs a preg_replace call on the input value and stores the result in the output value
 *
 * @see https://www.php.net/manual/en/function.preg-replace.php
 */
class FindDefaultLocationTypeId extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('location_type_id', 'Integer', E::ts('Default Location Type ID')),
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
   * @throws ExecutionException
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output): void {
    try {
      $locationType = \Civi\Api4\LocationType::get(FALSE)
        ->addSelect('id')
        ->addWhere('is_default', '=', TRUE)
        ->execute()->first();
      if ($locationType['id']) {
        $output->setParameter('location_type_id', $locationType['id']);
      }
    }
    catch (\CRM_Core_Exception $ex) {
      throw new ExecutionException(E::ts("Could not find a default location type ID."));
    }
  }
}
