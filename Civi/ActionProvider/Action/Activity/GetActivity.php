<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractGetSingleAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetActivity extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Activity';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('id');
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
    $bag = parent::getOutputSpecification();
    $target_contact_id = $bag->getSpecificationByName('target_contact_id');
    if ($target_contact_id instanceof Specification) {
      $target_contact_id->setMultiple(TRUE);
    }
    return $bag;
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    parent::setOutputFromEntity($entity, $output);

    $sql = "SELECT DISTINCT contact_id, record_type_id FROM civicrm_activity_contact WHERE activity_id = %1 AND record_type_id IN (1,3)";
    $sqlParams[1] = array($entity['id'], 'Integer');
    $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
    $target_contact_ids = array();
    $assignee_contact_id = null;
    while($dao->fetch()) {
      if ($dao->record_type_id == 1) {
        $assignee_contact_id = $dao->contact_id;
      } elseif ($dao->record_type_id == 3) {
        $target_contact_ids[] = $dao->contact_id;
      }
    }
    $sql = "SELECT DISTINCT case_id from civicrm_case_activity WHERE activity_id = %1";
    $sqlParams[1] = array($entity['id'], 'Integer');
    $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
    $case_id = array();
    while($dao->fetch()) {
      $case_id[] = $dao->case_id;
    }
    $output->setParameter('target_contact_id', $target_contact_ids);
    if ($assignee_contact_id) {
      $output->setParameter('assignee_contact_id', $assignee_contact_id);
    }
    if(!empty($case_id)){
      if(count($case_id)){
        $case_id = reset($case_id);
      }
      $output->setParameter('case_id', $case_id);
    }
  }



}
