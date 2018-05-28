<?php

namespace Civi\ActionProvider\Parameter;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;

class ParameterBag implements ParameterBagInterface, \IteratorAggregate {
	
	protected $parameters = array();
	
	/**
	 * Get the parameter.
	 */
	public function getParameter($name) {
		if (isset($this->parameters[$name])) {
			return $this->parameters[$name];
		}
		return null;
	}	
	/**
	 * Tests whether the parameter with the name exists.
	 */
	public function doesParameterExists($name) {
		if (isset($this->parameters[$name]) && $this->parameters[$name] != null) {
			return true;
		}
		return false;
	}
	
	/**
	 * Sets parameter. 
	 */
	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}
	
	public function getIterator() {
    return new \ArrayIterator($this->parameters);
  }
	
	/**
	 * Converts the object to an array.
	 * 
	 * @return array
	 */
	public function toArray() {
		return $this->parameters;
	}
	
}
