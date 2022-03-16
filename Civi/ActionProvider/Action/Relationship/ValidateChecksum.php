<?php

namespace Civi\ActionProvider\Action\Relationship;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class ValidateChecksum extends AbstractAction {

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
   * @throws
	 */
	protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
	  $contactIdA = $parameters->getParameter('contact_id_a');
    $checkSumA = $parameters->getParameter('checksum_a');
    $contactIdB = $parameters->getParameter('contact_id_b');
    $checkSumB = $parameters->getParameter('checksum_b');
    $output->setParameter('contact_id_a', $contactIdA);
    $output->setParameter('checksum_a', $checkSumA);
    $output->setParameter('contact_id_b', $contactIdB);
    $output->setParameter('checksum_b', $checkSumB);
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag([
      new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, False),
    ]);
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		$specs = new SpecificationBag();
    $specs->addSpecification(new Specification('checksum_a', 'String', E::ts('Checksum Contact A'), TRUE, NULL));
    $specs->addSpecification(new Specification('contact_id_a', 'Integer', E::ts('ContactID A'), TRUE, NULL));
    $specs->addSpecification(new Specification('checksum_b', 'String', E::ts('Checksum Contact B'), TRUE, NULL));
    $specs->addSpecification(new Specification('contact_id_b', 'Integer', E::ts('ContactID B'), TRUE, NULL));
    return $specs;
	}

  /**
   * @param ParameterBagInterface $parameters
   * @return bool
   * @throws InvalidParameterException
   */
	public function validateParameters(ParameterBagInterface $parameters) {
	  $contactIdA = $parameters->getParameter('contact_id_a');
	  $checksumA = $parameters->getParameter('checksum_a');
    $contactIdB = $parameters->getParameter('contact_id_b');
    $checksumB = $parameters->getParameter('checksum_b');
	  $validA = \CRM_Contact_BAO_Contact_Utils::validChecksum($contactIdA, $checksumA);
    $validB = \CRM_Contact_BAO_Contact_Utils::validChecksum($contactIdB, $checksumB);
	  if (!$validA || !$validB) {
	    throw new InvalidParameterException(E::ts('Invalid checksum, can not access contact data.'));
    }

    $relationshipTypeId = $this->relationshipTypeIds[$this->configuration->getParameter('relationship_type_id')];
    $relationshipFindParams = array();
    $relationshipFindParams['contact_id_a'] = $contactIdA;
    $relationshipFindParams['contact_id_b'] = $contactIdB;
    $relationshipFindParams['relationship_type_id'] = $relationshipTypeId;
    $relationshipFindParams['is_active'] = '1';
    $relationshipFindParams['options']['limit'] = 1;
    try {
      $relationship = civicrm_api3('Relationship', 'getsingle', $relationshipFindParams);
    } catch (\Exception $e) {
      throw new InvalidParameterException(E::ts('Invalid checksum, can not access contact data.'));
    }

    return TRUE;
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
			new Specification('contact_id_a', 'Integer', E::ts('Contact ID A'), TRUE),
      new Specification('checksum_a', 'Integer', E::ts('Checksum Contact A'), TRUE),
      new Specification('contact_id_b', 'Integer', E::ts('Contact ID B'), TRUE),
      new Specification('checksum_b', 'Integer', E::ts('Checksum Contact B'), TRUE)
		]);
	}

}
