<?php

namespace Civi\ActionProvider\Action;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class AddToGroup extends AbstractAction {
	
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
		civicrm_api3('GroupContact', 'create', array(
			'contact_id' => $parameters->getParameter('contact_id'),
			'group_id' => $this->configuration->getParameter('group_id'),
		));
		
		$output->setParameter('contact_id', $parameters->getParameter('contact_id')); 
	}
	
	/**
	 * Returns the specification of the configuration options for the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array(
			new Specification('group_id', 'Integer', E::ts('Select group'), true, null, 'Group', null, FALSE),
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
	 * Returns the human readable title of this action
	 */
	public function getTitle() {
	 	return E::ts('Add to group'); 
	}
	
	/**
	 * Returns the tags for this action.
	 */
	public function getTags() {
		return array(
			AbstractAction::SINGLE_CONTACT_ACTION_TAG,
			'GroupContactAdd', // This how this action is called in CiviRules
		);
	}
	
}
