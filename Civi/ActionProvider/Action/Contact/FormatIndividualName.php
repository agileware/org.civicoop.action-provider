<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class FormatIndividualName extends AbstractAction {

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
	  $firstName = $parameters->getParameter('first_name');
    $lastName = $parameters->getParameter('last_name');
    $firstParts = explode(" ", $firstName);
    foreach ($firstParts as $firstKey => $firstValue) {
      $firstParts[$firstKey] = ucfirst(strtolower($firstValue));
    }
    $lastParts = explode(" ", $lastName);
    foreach ($lastParts as $lastKey => $lastValue) {
      $lastParts[$lastKey] = ucfirst(strtolower($lastValue));
    }
    $output->setParameter('formatted_first_name', implode(" ", $firstParts));
    $output->setParameter('formatted_last_name', implode(" ", $lastParts));
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
    $specs->addSpecification(new Specification('first_name', 'String', E::ts('First Name'), TRUE, NULL));
    $specs->addSpecification(new Specification('last_name', 'String', E::ts('Last Name'), TRUE, NULL));
    return $specs;
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
			new Specification('formatted_first_name', 'String', E::ts('Formatted First Name'), TRUE),
			new Specification('formatted_last_name', 'String', E::ts('Formatted Last Name'), TRUE),
		]);
	}

}
