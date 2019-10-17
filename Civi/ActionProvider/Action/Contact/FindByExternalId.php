<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class FindByExternalId extends AbstractAction {

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
		$apiParams = array();
    if ($parameters->doesParameterExists('external_identifier') && $parameters->getParameter('external_identifier')) {
      $apiParams['external_identifier'] = $parameters->getParameter('external_identifier');
    }
		if (!count($apiParams)) {
		  throw new InvalidParameterException(E::ts("No parameter given"));
    }

    $apiParams['contact_type'] = $contact_type['name'];
    if ($contact_sub_type) {
      $apiParams['contact_sub_type'] = $contact_sub_type['name'];
    }
    $apiParams['return'] = 'id';
    try {
      $contact_id = civicrm_api3('Contact', 'getvalue', $apiParams);
      $output->setParameter('contact_id', $contact_id);
    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing
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
		$specs = new SpecificationBag();
    $specs->addSpecification(new Specification('external_identifier', 'String', E::ts('External ID'), true, null));
    return $specs;
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
