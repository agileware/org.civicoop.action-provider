<?php

namespace Civi\ActionProvider\Action\Group;

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
	  if ($this->configuration->getParameter('group_id')) {
      $group_id = $this->configuration->getParameter('group_id');
    } else {
	    $group_id = civicrm_api3('Group', 'getvalue', [
	      'return' => 'id',
        'name' => $this->configuration->getParameter('group_name'),
        'options' => ['limit' => 1]
      ]);
    }
		civicrm_api3('GroupContact', 'create', array(
			'contact_id' => $parameters->getParameter('contact_id'),
			'group_id' => $group_id,
		));
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array(
			new Specification('group_id', 'Integer', E::ts('Select group'), false, null, 'Group', null, FALSE),
      new Specification('group_name', 'String', E::ts('Or group name'), false, null, null, null, FALSE),
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

}
