<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class FindOrCreateContactByEmail extends AbstractAction {
	
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
		$contact_type = civicrm_api3('ContactType', 'getsingle', array('id' => $this->configuration->getParameter('contact_type')));
		$contact_sub_type = false;
		if (isset($contact_type['parent_id']) && $contact_type['parent_id'] > 0) {
			$contact_sub_type = $contact_type;
			$contact_type = civicrm_api3('ContactType', 'getsingle', array('id' => $contact_sub_type['parent_id']));
		}
		
		try {
			$params['email'] = $parameters->getParameter('email');
			$params['contact_type'] = $contact_type['name'];
			if ($contact_sub_type) {
				$params['contact_sub_type'] = $contact_sub_type['name'];
			}
			$params['return'] = 'id';
			$contact_id = civicrm_api3('Contact', 'getvalue', $params);
		} catch (\Exception $e) {
			$createParams['email'] = $parameters->getParameter('email');
			$createParams['contact_type'] = $contact_type['name'];
			if ($contact_sub_type) {
				$createParams['contact_sub_type'] = $contact_sub_type['name'];
			}
			$result = civicrm_api3('Contact', 'create', $createParams);
			$contact_id = $result['id'];
		}
				
		$output->setParameter('contact_id', $contact_id); 
	}
	
	/**
	 * Returns the specification of the configuration options for the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {	
		return new SpecificationBag(array(
			new Specification('contact_type', 'Integer', E::ts('Contact type'), true, null, 'ContactType', null, FALSE),
		));
	}
	
	/**
	 * Returns the specification of the parameters of the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		return new SpecificationBag(array(
			new Specification('email', 'String', E::ts('E-mail'), true)
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
		return new SpecificationBag(array(
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
		));
	}
	
	/**
	 * Returns the human readable title of this action
	 */
	public function getTitle() {
	 	return E::ts('Find or create contact by e-mail'); 
	}
	
	/**
	 * Returns the tags for this action.
	 */
	public function getTags() {
		return array(
			AbstractAction::SINGLE_CONTACT_ACTION_TAG,
			AbstractAction::DATA_MANIPULATION_TAG,
		);
	}
	
}
