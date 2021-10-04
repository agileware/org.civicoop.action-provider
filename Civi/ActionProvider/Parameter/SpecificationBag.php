<?php

namespace Civi\ActionProvider\Parameter;

use \Civi\ActionProvider\Exception\InvalidParameterException;
use CRM_ActionProvider_ExtensionUtil as E;

class SpecificationBag implements \IteratorAggregate  {

  /**
   * @var array SpecificationInterface[]
   */
	protected $parameterSpecifications = array();

	public function __construct($specifcations = array()) {
		foreach($specifcations as $spec) {
			$this->parameterSpecifications[$spec->getName()] = $spec;
		}
	}

	/**
	 * Validates the parameters.
	 *
	 * @param ParameterBagInterface $parameters
	 * @param SpecificationBag $specification
	 *
	 * @return bool
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
	 */
	public static function validate(ParameterBagInterface $parameters, SpecificationBag $specification) {
		foreach($specification as $spec) {
			// First check whether the value is present and should be present.
			if ($spec->isRequired() && !$parameters->doesParameterExists($spec->getName())) {
			  throw new InvalidParameterException($spec->getName(). ' is required');
			}

      if($parameters->doesParameterExists($spec->getName())) {
        $value = $parameters->getParameter($spec->getName());
        $spec->validate($value);
      }
		}
		return true;
	}

	/**
	 * @param SpecificationInterface $specification
	 *   The specification object.
	 * @return SpecificationBag
	 */
	public function addSpecification(SpecificationInterface $specification) {
		$this->parameterSpecifications[$specification->getName()] = $specification;
		return $this;
	}

	/**
	 * @param SpecificationInterface $specification
	 *   The specification object.
	 * @return SpecificationBag
	 */
	public function removeSpecification(SpecificationInterface $specification) {
		foreach($this->parameterSpecifications as $key => $spec) {
			if ($spec == $specification) {
				unset($this->parameterSpecifications[$key]);
			}
		}
		return $this;
	}

	/**
	 * @param string $name
	 *   The name of the parameter.
	 * @return SpecificationBag
	 */
	public function removeSpecificationbyName($name) {
		foreach($this->parameterSpecifications as $key => $spec) {
			if ($spec->getName() == $name) {
				unset($this->parameterSpecifications[$key]);
			}
		}
		return $this;
	}

	/**
	 * @param string $name
	 *   The name of the parameter.
	 * @return SpecificationInterface|null
	 */
  public function getSpecificationByName($name) {
    foreach ($this->parameterSpecifications as $key => $spec) {
      /* check if the specification is a composite object (an object with a method getSpecificationBag)
         if so search this bag with a recursive call
      */
      if (method_exists($spec, 'getSpecificationBag')) {
        $parameterSpecifications = $spec->getSpecificationBag()->getSpecificationByName($name);
        if ($parameterSpecifications) {
          return $parameterSpecifications;
        }
      } else if ($spec->getName() == $name) {
        return $this->parameterSpecifications[$key];
      }
    }
    return null;
  }


  public function getIterator() {
    return new \ArrayIterator($this->parameterSpecifications);
  }

	/**
	 * Converts the object to an array.
	 *
	 * @return array
	 */
	public function toArray() {
		$return = array();
		foreach($this->parameterSpecifications as $spec) {
		  $return[] = $spec->toArray();
		}
		return $return;
	}

}
