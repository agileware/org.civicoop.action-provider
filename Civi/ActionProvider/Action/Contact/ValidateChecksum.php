<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class ValidateChecksum extends AbstractAction {

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
	  $contactId = $parameters->getParameter('cid');
    $checkSum = $parameters->getParameter('cs');
    $output->setParameter('contact_id', $contactId);
    $output->setParameter('cs', $checkSum);
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
		$specs = new SpecificationBag();
    $specs->addSpecification(new Specification('cs', 'String', E::ts('Checksum'), TRUE, NULL));
    $specs->addSpecification(new Specification('cid', 'Integer', E::ts('ContactID'), TRUE, NULL));
    return $specs;
	}

  /**
   * @param ParameterBagInterface $parameters
   * @return bool
   * @throws InvalidParameterException
   */
	public function validateParameters(ParameterBagInterface $parameters) {
	  $contactId = $parameters->getParameter('cid');
	  $checksum = $parameters->getParameter('cs');
	  $valid = \CRM_Contact_BAO_Contact_Utils::validChecksum($contactId, $checksum);
	  if (!$valid) {
	    throw new InvalidParameterException(E::ts('Invalid checksum, can not access contact data.'));
    }
    return TRUE;
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
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE),
      new Specification('cs', 'Integer', E::ts('Checksum'), TRUE)
		]);
	}

}
