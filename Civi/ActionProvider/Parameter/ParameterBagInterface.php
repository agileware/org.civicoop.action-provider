<?php

namespace Civi\ActionProvider\Parameter;

interface ParameterBagInterface {
	
	/**
	 * Get the parameter.
	 */
	public function getParameter($name);
	
	/**
	 * Tests whether the parameter with the name exists.
	 */
	public function doesParameterExists($name);
	
	/**
	 * Sets parameter. 
	 */
	public function setParameter($name, $value);
	
}
