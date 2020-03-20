<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class SaveMaxContactId extends AbstractAction {

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
	  $contactId = \CRM_Core_DAO::singleValueQuery("SELECT MAX(id) FROM civicrm_contact");
	  if ($contactId) {
	    $output->setParameter('max_contact_id', (int) $contactId);
    }
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag();
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
    return new SpecificationBag();
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
			new Specification('max_contact_id', 'Integer', E::ts('Max Contact ID'), TRUE)
		]);
	}

}
