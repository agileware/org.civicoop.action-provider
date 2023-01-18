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
    $output->setParameter('formatted_first_name', $this->restructureName($firstName));
    $output->setParameter('formatted_last_name', $this->restructureName($lastName));
	}

  /**
   * Method to restructure name with separators ' '(space) and '-'
   *
   * @param string $name
   * @return string
   */
  public function restructureName(string $name): string {
    $parts = preg_split('/[ -.]/', $name);
    $formattedParts = [];
    $length = 0;
    foreach ($parts as $value) {
      if (!$length) {
        $length = strlen($value);
      }
      else {
        $length = $length + strlen($value) + 1;
      }
      $formattedParts[] = ucfirst(strtolower($value));
      $formattedParts[] = substr($name, $length, 1);
    }
    return implode("", $formattedParts);
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

  /**
   * Method to set the help text
   *
   * @return string
   */
  public function getHelpText(): string {
    return E::ts("This action will split the input of first and last name into elements between spaces or '-', '.' and single quotes and change each elements to be lower key apart from the first character which will be capitals. For example john peter as first name and rhys-jones as last name will become John Peter Rhys-Jones");
  }

}
