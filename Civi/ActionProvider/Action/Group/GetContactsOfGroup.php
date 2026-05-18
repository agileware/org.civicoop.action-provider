<?php
/**
 * @author Jon Goldberg <jon@megaphonetech.com>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Group;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetContactsOfGroup extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('group_id', 'Integer', E::ts('Group ID'), TRUE, NULL, 'Group', NULL, FALSE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    $bag->addSpecification(new Specification('contact_ids', 'Integer', E::ts('Contact IDs'), FALSE, NULL, NULL, FALSE, TRUE));
    $bag->addSpecification(new Specification('first_contact_id', 'Integer', E::ts('First found contact ID'), FALSE, NULL, NULL, FALSE, FALSE));
    return $bag;
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   * @param ParameterBagInterface $output
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $group_id = $parameters->getParameter('group_id');

    $contacts = \Civi\Api4\Contact::get(FALSE)
      ->addSelect('id')
      ->addWhere('groups', 'IN', $group_id)
      ->execute()
      ->column('id');

    $output->setParameter('first_contact_id', $contacts[0] ?: NULL);
    $output->setParameter('contact_ids', $contacts);
  }

}
