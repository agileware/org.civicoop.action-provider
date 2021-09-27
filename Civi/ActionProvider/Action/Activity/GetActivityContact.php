<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetActivityContact extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $bag = new SpecificationBag();
    $bag->addSpecification(new OptionGroupSpecification('record_type', 'activity_contacts', E::ts('Record type'), false, null, true));
    return $bag;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Activity ID'), true),
    ]);
    return $bag;
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
    $bag->addSpecification(new Specification('contact_ids', 'Integer', E::ts('Contact IDs'), false, null, null, false, true));
    $bag->addSpecification(new Specification('first_contact_id', 'Integer', E::ts('First found contact ID'), false, null, null, false, false));
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
    $id = $parameters->getParameter('id');
    $record_type_ids = $this->configuration->getParameter('record_type');
    $sql = "SELECT DISTINCT contact_id FROM civicrm_activity_contact WHERE activity_id = %1";
    $sqlParams[1] = array($id, 'Integer');
    if ($record_type_ids) {
      if (is_array($record_type_ids)) {
        if (count($record_type_ids)) {
          $sql .= " AND record_type_id IN (".implode(", ", $record_type_ids).")";
        }
      }
      else {
        $sql .= " AND record_type_id = %2";
        $sqlParams[2] = [(int) $record_type_ids, 'Integer'];
      }
    }

    $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
    $contact_ids = array();
    while($dao->fetch()) {
      $contact_ids[] = $dao->contact_id;
    }

    $output->setParameter('first_contact_id', reset($contact_ids));
    $output->setParameter('contact_ids', $contact_ids);
  }



}
