<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Relationship;
use CRM_ActionProvider_ExtensionUtil as E;

class EmployerId {
  public static function updateEmployerId(array $relationshipParams): void {
    if ($relationshipParams['relationship_type_id'] && $relationshipParams['contact_id_b'] && $relationshipParams['relationship_type_id'] && $relationshipParams['contact_id_b']) {
      $emp = new EmployerId();
      if ($relationshipParams['relationship_type_id'] == $emp->getEmployerRelationshipTypeId()) {
        $emp->setContactEmployerId($relationshipParams['contact_id_a'], $relationshipParams['contact_id_b']);
      }
    }
  }

  /**
   * Set the employer id to contactB for contactA
   *
   * @param int $contactA
   * @param int $contactB
   * @return void
   */
  public function setContactEmployerId(int $contactA, int $contactB): void {
    try {
      \Civi\Api4\Contact::update(FALSE)
        ->addWhere('id' , '=', $contactA)
        ->addValue('employer_id', $contactB)
        ->execute();
    }
    catch (\CRM_Core_Exception $ex) {
      \Civi::log()->error(E::ts("Could not update contact ID ") . $contactA . E::ts(" with employer ID ") . $contactB
        . E::ts(" in ") . __METHOD__ . E::ts(", error message from API4 Contact update: ") . $ex->getMessage());
    }
  }

  /**
   * Method to get employer relationship type id
   *
   * @return int|null
   */
  public function getEmployerRelationshipTypeId(): ?int {
    $relationshipTypeId = NULL;
    try {
      $relationShipType = \Civi\Api4\RelationshipType::get(FALSE)
        ->addSelect('id')
        ->addWhere('name_a_b', '=', 'Employee of')
        ->addWhere('name_b_a', '=', 'Employer of')
        ->execute()->first();
      if ($relationShipType['id']) {
        $relationshipTypeId = $relationShipType['id'];
      }
    }
    catch (\CRM_Core_Exception $ex) {
    }
    return $relationshipTypeId;
  }

}
