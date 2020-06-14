<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class FindContactByEmail extends AbstractAction {

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
  public function getHelpText() {
    return E::ts('Returns the first found contact with an e-mail address');
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
		$contact_type = ContactActionUtils::getContactType($this->configuration->getParameter('contact_type'));
		try {
			$params['email'] = $parameters->getParameter('email');
			$params['contact_type'] = $contact_type['contact_type']['name'];
			if ($contact_type['contact_sub_type']) {
				$params['contact_sub_type'] = $contact_type['contact_sub_type']['name'];
			}
			$params['options']['limit'] = 1;
			$params['options']['sort'] = 'id ASC';

			$result = civicrm_api3('Contact', 'get', $params);
			$contact = reset($result['values']);
			if (!empty($contact['id'])) {
        $output->setParameter('contact_id', $contact['id']);
      }
		} catch (\Exception $e) {

		}
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

}
