<?php

namespace Civi\ActionProvider\Parameter;

interface ParameterBagInterface {

	/**
	 * Get the parameter.
	 */
	public function getParameter($name);

	/**
	 * Legacy name for parameterExists.
	 *
	 * @deprecated
	 * @see parameterExists
	 */
	public function doesParameterExists($name);

	/**
	 * Tests whether the parameter with the name exists.
	 */
	public function parameterExists($name);

	/**
	 * Sets parameter.
	 */
	public function setParameter($name, $value);

	/**
	 * Converts the object to an array.
	 *
	 * @return array
	 */
	public function toArray();

}
