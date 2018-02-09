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
	 * @return void
	 */
	protected function doAction(ParameterBagInterface $parameters) {
		civicrm_api3('GroupContact', 'create', array(
			'contact_id' => $parameters->getParameter('contact_id'),
			'group_id' => $this->configuration->getParameter('group_id'),
		));
	}
	
	/**
	 * Returns the specification of the configuration options for the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array(
			new Specification('group_id', 'Integer', E::ts('Group ID'), true, null, 'Group', null, TRUE),
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
	
}
