<?php

/**
 * Returns a list of all contacts that are connected to a case
 * with the configured relationship. Complements RoleGroupSync
 *
 * @author Klaas Eikelboom  <klaas.eikelboom@civicoop.org>
 * @date 04-May-2022
 * @license  AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class GetRoleGroup extends AbstractAction {

  protected $relationshipTypes = [];

  public function __construct() {
    parent::__construct();
    $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $this->relationshipTypes = array();
    $this->relationshipTypeIds = array();
    foreach($relationshipTypesApi['values'] as $relType) {
      $this->relationshipTypes[$relType['id']] = $relType['label_a_b'];
    }
  }

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    /**
     * The parameters given to the Specification object are:
     *
     * @param string $name
     * @param string $dataType
     * @param string $title
     * @param bool $required
     * @param mixed $defaultValue
     * @param string|null $fkEntity
     * @param array $options
     * @param bool $multiple
     */
    return new SpecificationBag([
      new Specification('relationship_type_id', 'String', E::ts('Role'), true, null, null, $this->relationshipTypes, False),
      new Specification('client', 'String', E::ts('Client '), true, null, null, ['a'=>'Contact A', 'b'=>'Contact B'], False)
    ]);
  }

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), TRUE, NULL, NULL, NULL, FALSE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(
      [new Specification('contact_ids', 'String', E::ts('Inserted Ids'), FALSE)]
    );
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {

    $caseId = $parameters->getParameter('case_id');
    $relationshipTypeId = $this->getConfiguration()->getParameter('relationship_type_id');
    // A role is just a relationship that is connected to a case. The client can be
    // connected to the a or to the b side
    // below fills two variables so that the remainder of the code
    // can be direction independent.
    if($this->getConfiguration()->getParameter('client')==='a'){
      $clientContactColumn = 'contact_id_a';
      $contactColumn = 'contact_id_b';
    } else {
      $clientContactColumn = 'contact_id_b';
      $contactColumn = 'contact_id_a';
    }
    $clientId = civicrm_api3('Case', 'getvalue', [
      'return' => 'contact_id',
      'id' => $caseId,
      'options' => ['limit' => 1]
    ]);
    $clientId = reset($clientId);
    // load the list with current contacts that are
    // connected by the configured role
    $relationShips = civicrm_api3('Relationship','get', [
      'relationship_type_id' => $relationshipTypeId,
      'case_id' => $caseId,
       $clientContactColumn => $clientId,
      'options' => ['limit' => 0 ]
    ])['values'];

    $currentContacts = [];
    foreach($relationShips as $relationShp){
      $currentContacts[]= $relationShp[$contactColumn];
    }
    // output some feedback for testing purposes
    $output->setParameter('contact_ids', $currentContacts);
  }
}
