<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class FindContactWithRoleOnCase extends AbstractAction {

  protected $relationshipTypes = array();
  protected $relationshipTypeIds = array();

  public function __construct() {
    parent::__construct();
    $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $this->relationshipTypes = array();
    $this->relationshipTypeIds = array();
    foreach($relationshipTypesApi['values'] as $relType) {
      $this->relationshipTypes[$relType['name_a_b']] = $relType['label_a_b'];
      $this->relationshipTypeIds[$relType['name_a_b']] = $relType['id'];
    }
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
    $case_id = $parameters->getParameter('case_id');
    $type_id = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
    try {
      $case = civicrm_api3('Case', 'getsingle', ['id' => $case_id]);
      $relationshipFindParams = array();
      $relationshipFindParams['relationship_type_id'] = $type_id;
      $relationshipFindParams['is_active'] = '1';
      $relationshipFindParams['case_id'] = $case_id;
      $relationshipFindParams['options']['sort']['contact_id_b ASC'];
      $relationshipFindParams['options']['offset'] = 0;
      $relationshipFindParams['options']['limit'] = 1;
      $relationship = civicrm_api3('Relationship', 'getsingle', $relationshipFindParams);
      $output->setParameter('contact_id', $relationship['contact_id_b']);
    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing.
    }
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ]);
  }


  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new Specification('relationship_type_id', 'String', E::ts('Role'), true, null, null, $this->relationshipTypes, False),
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), true),
    ]);
  }


}