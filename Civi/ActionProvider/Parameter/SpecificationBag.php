<?php

namespace Civi\ActionProvider\Parameter;

class SpecificationBag implements \IteratorAggregate  {
	
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
	 * @return bool
	 */
	public static function validate(ParameterBagInterface $parameters, SpecificationBag $specification) {
		foreach($specification as $spec) {
			// First check whether the value is present and should be present.
			if ($spec->isRequired() && !$parameters->doesParameterExists($spec->getName())) {
			  return false;
			} if($parameters->doesParameterExists($spec->getName())) {
			  $value = $parameters->getParameter($spec->getName());
        if ($value && !\CRM_Utils_Type::validate($value, $spec->getDataType(), false)) {
          return false;
        }  
			}
		}
		return true;
	}
	
	/**
	 * @param Specification $specification
	 *   The specification object.
	 * @return SpecificationBag
	 */
	public function addSpecification(Specification $specification) {
		$this->parameterSpecifications[$specification->getName()] = $specification;
		return $this;
	}
	
	/**
	 * @param Specification $specification
	 *   The specification object.
	 * @return SpecificationBag
	 */
	public function removeSpecification(Specification $specification) {
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
	 * @return Specification|null
	 */
	public function getSpecificationByName($name) {
		foreach($this->parameterSpecifications as $key => $spec) {
			if ($spec->getName() == $name) {
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
