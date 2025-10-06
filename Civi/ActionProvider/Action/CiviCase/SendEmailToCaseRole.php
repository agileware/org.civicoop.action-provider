<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\Communication\SendEmail;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class SendEmailToCaseRole extends SendEmail {

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
   * 	 The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $case_id = $parameters->getParameter('case_id');
    $type_id = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
    try {
      $relationshipFindParams = array();
      $relationshipFindParams['relationship_type_id'] = $type_id;
      $relationshipFindParams['is_active'] = '1';
      $relationshipFindParams['case_id'] = $case_id;
      $relationshipFindParams['options']['sort']['contact_id_b ASC'];
      $relationshipFindParams['options']['offset'] = 0;
      $relationshipFindParams['options']['limit'] = 0;
      $relationships = civicrm_api3('Relationship', 'get', $relationshipFindParams);
      $contactIds = [];
      foreach($relationships['values'] as $relationship) {
        $contactIds[] = $relationship['contact_id_b'];
      }

      if(count($contactIds)) {
        $this->sendEmailToContacts($contactIds, $parameters);
      }
    } catch (\CRM_Core_Exception $ex) {
      // Do nothing.
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $specs = parent::getConfigurationSpecification();
    $specs->addSpecification(new Specification('relationship_type_id', 'String', E::ts('Role'), true, null, null, $this->relationshipTypes, False));
    return $specs;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = parent::getParameterSpecification();
    /** @var Specification */
    $caseId = $specs->getSpecificationByName('case_id');
    $caseId->setRequired(TRUE);
    /** @var Specification */
    $contactId = $specs->getSpecificationByName('contact_id');
    $specs->removeSpecification($contactId);
    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }

}
