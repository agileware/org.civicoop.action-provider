<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class FindHouseholdByIndividualId extends AbstractAction {

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText() {
    return E::ts('Returns the first found household by one of the household members');
  }

	/**
	 * Run the action
	 *
	 * @param ParameterInterface $parameters
	 *   The parameters to this action.
	 * @param ParameterBagInterface $output
	 * 	 The parameters this action can send back
	 * @return void
	 */
	protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
		$contactId = $parameters->getParameter('contact_id');
		try {
			$relationshipCache = \Civi\Api4\RelationshipCache::get(TRUE)
				->addSelect('near_contact_id')
				->addWhere('far_relation:name', 'IN', $this->configuration->getParameter('relationship_type'))
				->addWhere('far_contact_id', '=', $contactId)
				->addWhere('is_active', '=', TRUE)
				->addWhere('near_contact_id.contact_type', '=', 'Household')
				->addWhere('far_contact_id.contact_type', '=', 'Individual')
				->addOrderBy('near_contact_id', 'ASC')
				->setLimit(1)
				->execute()
				->single();
			$output->setParameter('household_id', $relationshipCache['near_contact_id']);
		} catch (\Exception $e) {

		}
	}

	protected function getRelationshipTypes(): array {
		static $relationshipTypes = null;
		if ($relationshipTypes === null) {
			$relationshipTypesApi = civicrm_api3('RelationshipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    	$relationshipTypes = [];
    	foreach($relationshipTypesApi['values'] as $relType) {
      	$relationshipTypes[$relType['name_a_b']] = $relType['label_a_b'];
				$relationshipTypes[$relType['name_b_a']] = $relType['label_b_a'];
    	}
		}
		return $relationshipTypes;
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array(
      new Specification('relationship_type', 'String', E::ts('Relationship type for related households'), true, null, null, $this->getRelationshipTypes(), true),
    ));
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		return new SpecificationBag(array(
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
		));
	}

	/**
	 * Returns the specification of the output parameters of this action.
	 *
	 * This function could be overridden by child classes.
	 *
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		return new SpecificationBag(array(
			new Specification('contact_id', 'Integer', E::ts('Household ID'), true)
		));
	}

}
