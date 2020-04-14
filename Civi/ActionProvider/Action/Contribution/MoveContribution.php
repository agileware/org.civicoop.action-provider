<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\ParameterBagInterface;

use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class MoveContribution extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $new_contact_id = $parameters->getParameter('new_contact_id');
    $old_contact_id = $parameters->getParameter('old_contact_id');
    $sql = "SELECT id FROM civicrm_contribution WHERE contact_id = %1";
    $sqlParams[1] = [$old_contact_id, 'Integer'];
    $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while($dao->fetch()) {
      civicrm_api3('Contribution', 'create', [
        'id' => $dao->id,
        'contact_id' => $new_contact_id
      ]);
    }
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('old_contact_id', 'Integer', E::ts('Old Contact ID'), true),
      new Specification('new_contact_id', 'Integer', E::ts('New Contact ID'), true),
    ));
  }

}
