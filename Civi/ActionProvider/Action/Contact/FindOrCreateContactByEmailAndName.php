<?php
namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Exception\InvalidParameterException;

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Class FindOrCreateContactByEmailAndName
 *
 * Tries to find a contact by email, first name and last name. If it fails to find one, it will create a contact
 * using email, first name and last name
 *
 * @package Civi\ActionProvider\Action\Contact
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 8 Jan 2020
 * @license AGPL-3.0
 */

class FindOrCreateContactByEmailAndName extends AbstractAction {

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
		  if ($parameters->doesParameterExists('email')) {
        $params['email'] = $parameters->getParameter('email');
      }
			switch ($contactType['contact_type']['name']) {
        case 'Individual':
          $params['first_name'] = $parameters->getParameter('first_name');
          $params['last_name'] = $parameters->getParameter('last_name');
          break;
        case 'Household';
          $params['household_name'] = $parameters->getParameter('household_name');
          break;
        case 'Organization':
          $params['organization_name'] = $parameters->getParameter('organization_name');
          break;
      }
			$params['contact_type'] = $contactType['contact_type']['name'];
			if ($contactType['contact_sub_type']) {
				$params['contact_sub_type'] = $contactType['contact_sub_type']['name'];
			}
			$params['return'] = 'id';
			$contactId = civicrm_api3('Contact', 'getvalue', $params);
		} catch (\Exception $e) {
		  if ($parameters->doesParameterExists('email')) {
        $createParams['email'] = $parameters->getParameter('email');
      }
      switch ($contactType['contact_type']['name']) {
        case 'Individual':
          $createParams['first_name'] = $parameters->getParameter('first_name');
          $createParams['last_name'] = $parameters->getParameter('last_name');
          break;
        case 'Household';
          $createParams['household_name'] = $parameters->getParameter('household_name');
          break;
        case 'Organization':
          $createParams['organization_name'] = $parameters->getParameter('organization_name');
          break;
      }
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
   * @return bool
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  protected function validateParameters(ParameterBagInterface $parameters) {
    $valid = parent::validateParameters($parameters);
    if ($valid) {
      $contactType = ContactActionUtils::getContactType($this->configuration->getParameter('contact_type'));
      switch ($contactType['contact_type']['name']) {
        case 'Individual':
          if (!$parameters->doesParameterExists('first_name')) {
            throw new InvalidParameterException('first_name is required');
          }
          if (!$parameters->doesParameterExists('last_name')) {
            throw new InvalidParameterException('last_name is required');
          }
          break;
        case 'Household';
          if (!$parameters->doesParameterExists('household_name')) {
            throw new InvalidParameterException('household_name is required');
          }
          break;
        case 'Organization':
          if (!$parameters->doesParameterExists('organization_name')) {
            throw new InvalidParameterException('organization_name is required');
          }
          break;
      }

    }
    return $valid;
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
			new Specification('email', 'String', E::ts('E-mail'), FALSE),
			new Specification('first_name', 'String', E::ts('First Name'), FALSE),
			new Specification('last_name', 'String', E::ts('Last Name'), FALSE),
      new Specification('organization_name', 'String', E::ts('Organization name'), FALSE),
      new Specification('household_name', 'String', E::ts('Household name'), FALSE),
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
