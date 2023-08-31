<?php

namespace Civi\ActionProvider\Action\Group;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class AddToMultipleGroupsParameter extends AbstractAction {


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
		$groups = $parameters->getParameter('group_ids');
		foreach($groups as $groupIdToCheck) {
			civicrm_api3('GroupContact', 'create', array(
				'contact_id' => $parameters->getParameter('contact_id'),
				'group_id' => $groupIdToCheck
			));
		}
	}
  
	/**
	 * Returns the specification of the configuration options for the actual
	 * action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array());
	}
  
	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		return new SpecificationBag(array(
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
			new Specification('group_ids', 'Integer', E::ts('Group IDs'), true, null, 'Group', null, TRUE),
		));
	}
  
	/**
	 * Returns a help text for this action.
	 *
	 * The help text is shown to the administrator who is configuring the action.
	 * Override this function in a child class if your action has a help text.
	 *
	 * @return string|false
	 */
	public function getHelpText() {
		return E::ts('This action will add a single contact to multiple groups. The parameter Group IDs is a list');
	}

}