<?php
namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Class FindByEmailOrCreateEmailName
 *
 * Tries to find a contact by email. If it fails to find one, it will create a contact using email, first name and last name
 *
 * @package Civi\ActionProvider\Action\Contact
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 8 Jan 2020
 * @license AGPL-3.0
 */

class FindByEmailOrCreateEmailName extends AbstractAction {

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
    $contactType = ContactActionUtils::getContactType($this->configuration->getParameter('contact_type'));
		try {
			$params['email'] = $parameters->getParameter('email');
      $params['contact_type'] = $contactType['contact_type']['name'];
      if ($contactType['contact_sub_type']) {
        $params['contact_sub_type'] = $contactType['contact_sub_type']['name'];
      }
			$params['return'] = 'id';
			$contactId = civicrm_api3('Contact', 'getvalue', $params);
		} catch (\Exception $e) {
      $createParams['email'] = $parameters->getParameter('email');
      $createParams['first_name'] = $parameters->getParameter('first_name');
      $createParams['last_name'] = $parameters->getParameter('last_name');
      $createParams['contact_type'] = $contactType['contact_type']['name'];
      if ($contactType['contact_sub_type']) {
        $createParams['contact_sub_type'] = $contactType['contact_sub_type']['name'];
      }
			$result = civicrm_api3('Contact', 'create', $createParams);
			$contactId = $result['id'];
		}

		$output->setParameter('contact_id', $contactId);
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag([
			new Specification('contact_type', 'Integer', E::ts('Contact type'), TRUE, null, 'ContactType', NULL, FALSE),
		]);
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		return new SpecificationBag([
			new Specification('email', 'String', E::ts('E-mail'), TRUE),
			new Specification('first_name', 'String', E::ts('First Name'), TRUE),
			new Specification('last_name', 'String', E::ts('Last Name'), TRUE),
		]);
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
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE)
		]);
	}

}
