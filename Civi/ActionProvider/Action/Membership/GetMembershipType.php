<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Membership;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\Membership\Parameter\MembershipTypeSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetMembershipType extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new MembershipTypeSpecification('membership_type', E::ts('Membership Type'), TRUE),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
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
    $bag = new SpecificationBag();
    foreach(\CRM_Member_BAO_MembershipType::fields() as $field) {
      $type = \CRM_Utils_Type::typeToString($field['type']);
      switch($type) {
        case 'Int':
          $type = 'Integer';
          break;
      }
      $spec = new Specification($field['name'], $type, $field['title']);
      $bag->addSpecification($spec);
    }
    return $bag;
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
    $membershipType = new \CRM_Member_BAO_MembershipType();
    $membershipType->name = $this->configuration->getParameter('membership_type');
    if ($membershipType->find(true)) {
      foreach(\CRM_Member_BAO_MembershipType::fields() as $field) {
        $key = $field['name'];
        $output->setParameter($key, $membershipType->$key);
      }
    }
  }



}