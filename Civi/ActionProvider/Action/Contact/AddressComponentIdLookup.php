<?php
/**
 * @author Jon Goldberg <jon@megaphonetech.com>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class AddressComponentIdLookup extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return use Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getConfigurationSpecification() {
    $entities = [
      'country~name' => 'Country (as name)',
      'country~iso_code' => 'Country (as ISO code)',
      'state_province~name' => 'State/Province (as name)',
      'state_province~abbreviation' => 'State/Province (as abbreviation)',
    ];
    return new SpecificationBag([
      new Specification('entity_type', 'String', E::ts('Select the entity you wish to look up'), TRUE, NULL, NULL, $entities),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return use Civi\ActionProvider\Parameter\SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('value', 'Integer', E::ts('Entity ID'), TRUE, NULL, NULL, NULL, TRUE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('value', 'String', E::ts('Value')),
    ]);
  }

  /**
   * Run the action
   *
   * @param Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param Civi\ActionProvider\Parameter\ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $id = array_pop($parameters->getParameter('value'));
    [$entity, $field] = explode('~', $this->configuration->getParameter('entity_type'));

    if ($id && $entity && $field) {
      $result = civicrm_api3($entity, 'getvalue', ['return' => $field, 'id' => $id]);
      $output->setParameter('value', $result);
    }
  }

}
