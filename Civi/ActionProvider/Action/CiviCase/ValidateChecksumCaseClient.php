<?php

namespace Civi\ActionProvider\Action\CiviCase;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class ValidateChecksumCaseClient extends AbstractAction {

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
	  $caseId = $parameters->getParameter('case_id');
	  $output->setParameter('case_id', $caseId);
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
		$specs->addSpecification(new Specification('case_id', 'Integer', E::ts('Case ID'), true));
		return $specs;
	}

  /**
   * @param ParameterBagInterface $parameters
   * @return bool
   * @throws InvalidParameterException
   */
	public function validateParameters(ParameterBagInterface $parameters) {
	    $case_id = $parameters->getParameter('case_id');
	    try {
	      $case = civicrm_api4('CaseContact', 'get', [
		'select' => ['contact_id'],
		'where' => [['id', '=',  $case_id]],
		'limit' => 1,
		'checkPermissions' => FALSE,
		]);
	      $contactId = $case[0]['contact_id'];
	      $checksum = $parameters->getParameter('cs');
	      $valid = \CRM_Contact_BAO_Contact_Utils::validChecksum($contactId, $checksum);
		    if (!$valid) {
		throw new InvalidParameterException(E::ts('Invalid checksum, can not access contact data.'));
	      }
	    } catch (\CiviCRM_API3_Exception $ex) {
	      throw new InvalidParameterException(E::ts('Invalid checksum, can not access contact data.'));
	    }
	    return TRUE;
	  }

  /**
	 * Returns the specification of the output parameters of this action.
	 *
	 * This function could be overridden by child classes.
	 *
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		return new SpecificationBag([
			new Specification('case_id', 'Integer', E::ts('Case ID'), TRUE)
		]);
	}

}
