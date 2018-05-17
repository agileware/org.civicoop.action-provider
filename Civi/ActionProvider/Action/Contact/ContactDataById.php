<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class ContactDataById extends AbstractAction {
	
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
		$contact = civicrm_api3('Contact', 'getsingle', array('id' => $parameters->getParameter('contact_id')));
		foreach($contact as $field => $value) {
			$output->setParameter($field, $value);
		} 
	}
	
	/**
	 * Returns the specification of the configuration options for the actual action.
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
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
		));
	}
	
	/**
	 * Returns the specification of the output parameters of this action.
	 * 
	 * This function could be overriden by child classes.
	 * 
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		$bag = new SpecificationBag();
		$contact_fields = civicrm_api3('contact', 'getfields', array('action' => 'get', 'options' => array('limit' => 0)));
		foreach($contact_fields['values'] as $field) {
			if (empty($field['type'])) {
				continue;
			}
			$type = \CRM_Utils_Type::typeToString($field['type']);
			switch ($type) {
				case 'Int':
					$type = 'Integer';
					break;
			}
			$fieldSpec = new Specification(
				$field['name'],
				$type,
				$field['title'],
				false
			);
			$bag->addSpecification($fieldSpec);
		}
		
		return $bag;
	}
	
	/**
	 * Returns the human readable title of this action
	 */
	public function getTitle() {
	 	return E::ts('Get contact data by ID'); 
	}
	
	/**
	 * Returns the tags for this action.
	 */
	public function getTags() {
		return array(
			AbstractAction::SINGLE_CONTACT_ACTION_TAG,
			AbstractAction::DATA_RETRIEVAL_TAG,
		);
	}
	
}
