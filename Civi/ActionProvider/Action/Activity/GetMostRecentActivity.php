<?php
/**
 * Gets the most recent activity of a contact. So the contact_id is the key, the
 * activity id is returned.
 *
 * Returns the activity id and the contact id as well.
 *
 * @author Klaas Eikelboom  <klaas.eikelboom@civicoop.org>
 * @date 25-May-2020
 * @license  AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetMostRecentActivity extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $bag = new SpecificationBag();
    $bag->addSpecification(new OptionGroupSpecification('record_type', 'activity_contacts', E::ts('Record type'), false, null, true));
    $bag->addSpecification(new OptionGroupSpecification('activity_type_id', 'activity_type', E::ts('Activity Type'), false, null, true));
    $bag->addSpecification(new OptionGroupSpecification('status_id', 'activity_status', E::ts('Activity Status'), false, null, true));
    return $bag;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Contact ID'), true),
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
    $bag->addSpecification(new Specification('activity_id', 'Integer', E::ts('Activity ID'), false, null, null, false, true));
    $bag->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), false, null, null, false, true));
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
    $activity_type_ids = $this->configuration->getParameter('activity_type');
    $status_ids = $this->configuration->getParameter('status_id');
    $sql =<<< SQL
    SELECT activity_id
    FROM civicrm_activity_contact ac
    JOIN civicrm_activity act ON (act.id=ac.activity_id)
    WHERE contact_id = %1
SQL;
    $sqlParams[1] = array($id, 'Integer');
    if ($record_type_ids && is_array($record_type_ids) && count($record_type_ids)) {
      $sql .= " AND record_type_id IN (".implode(", ", $record_type_ids).")";
    }
    if ($activity_type_ids && is_array($activity_type_ids) && count($activity_type_ids)) {
      $sql .= " AND activity_type_id IN (".implode(", ", $activity_type_ids).")";
    }
    if ($status_ids && is_array($status_ids) && count($status_ids)) {
      $sql .= " AND status_id IN (".implode(", ", $status_ids).")";
    }
    $sql .= " ORDER BY act.activity_date_time DESC limit 1";
    $activity_id = \CRM_Core_DAO::singleValueQuery($sql,[
      1 => [$id,'Integer']
    ]);
    $output->setParameter('activity_id', $activity_id);
    $output->setParameter('contact_id', $id);
  }
}
