<?php

namespace Civi\ActionProvider\Action;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Exception\InvalidConfigurationException;

/**
 * This is the abstract class for an action.
 * 
 * Each action has a configuration which could be set in the user interface.
 * The parameters passed to the execute function are the data comming from the upper system such as the data in the trigger 
 * with civirules. Or the data in the table with SqlTasks. 
 * 
 */
abstract class AbstractAction implements \JsonSerializable {
	
	const DATA_MANIPULATION_TAG = 'data-manipulation';
	const SINGLE_CONTACT_ACTION_TAG = 'act-on-a-single-contact';
	const MULTIPLE_CONTACTS_ACTION_TAG = 'action-on-multiple-contacts';
	
	/**
	 * @var ParameterBag
	 */
	protected $configuration;
	
	/**
	 * @var ParameterBag
	 */
	protected $defaultConfiguration;
	
	public function __construct() {
		$this->configuration = new ParameterBag();
		$this->defaultConfiguration = new ParameterBag();

		foreach($this->getConfigurationSpecification() as $spec) {
			if ($spec->getDefaultValue()) {
				$this->configuration->set($spec->getName(), $spec->getDefaultValue());
				$this->defaultConfiguration->set($spec->getName(), $spec->getDefaultValue());
			}
		}
	}
	
	/**
	 * Run the action
	 * 
	 * @param ParameterInterface $parameters
	 *   The parameters to this action. 
	 * @return void
	 */
	abstract protected function doAction(ParameterBagInterface $parameters);
	
	/**
	 * Returns the specification of the configuration options for the actual action.
	 * 
	 * @return SpecificationBag
	 */
	abstract public function getConfigurationSpecification();
	
	/**
	 * Returns the specification of the parameters of the actual action.
	 * 
	 * @return SpecificationBag
	 */
	abstract public function getParameterSpecification();
	
	/**
	 * Returns the human readable title of this action
	 */
	abstract public function getTitle();
	
	/**
	 * Returns the system name of the action. 
	 * 
	 * We generate one based on the namespace of the class
	 * and the class name.
	 *  
	 * @return string
	 */
	public function getName() {
		$reflect = new \ReflectionClass($this);
		$className = $reflect->getShortName();
		return $className;
	}
	 
	/**
	 * Execute the action.
	 * 
	 * The execute method will first validate the given configuration and the given
	 * parameters against their specifcation.
	 * 
	 * After that it will fire the doAction method which is implemented in a child class to do the
	 * actual action.
	 * This method is basicly a wrapper around doAction.
	 * 
	 * @param ParameterBagInterface $parameters;
	 */
	public function execute(ParameterBagInterface $parameters) {
		if (!$this->validateConfiguration()) {
			throw new InvalidConfigurationException("Found invalid configuration for the action: ".$this->getTitle());
		}
		if (!$this->validateParameters($parameters)) {
			throw new InvalidParameterException("Found invalid configuration for the action: ".$this->getTitle());
		}
			
		$this->doAction($parameters);
	} 
	
	/**
	 * @return bool
	 */
	protected function validateParameters(ParameterBagInterface $parameters) {
		return SpecificationBag::validate($parameters, $this->getParameterSpecification());
	}
	
	/**
	 * @return bool;
	 */
	protected function validateConfiguration() {
		if ($this->configuration === null) {
			return false;
		}
		return SpecificationBag::validate($this->configuration, $this->getConfigurationSpecification());
	}
	
	/**
	 * @return ParameterBag
	 */
	public function getDefaultConfiguration() {
		return $this->defaultConfiguration;
	}
	
	/**
	 * @return ParameterBag
	 */
	public function getConfiguration() {
		return $this->configuration;
	}
	
	/**
	 * @param ParameterBag $configuration
	 */
	public function setConfiguration(ParameterBag $configuration) {
		$this->configuration = $configuration;
		return $this;
	}
	
	/**
	 * Returns a list with tags for this action. 
	 * 
	 * Each tag might indicate in what context this action might be used.
	 * Filtering which actions might be useful in a certain context
	 * is done by the Provider class or a child class of the provider. 
	 * 
	 * Tags could also be aliases of the action name.
	 * 
	 * @return array
	 */
	public function getTags() {
		return array();
	}
	
	/**
	 * Converts the object to an array.
	 * 
	 * @return array
	 */
	public function toArray() {
		$return['configuration_spec'] = $this->getConfigurationSpecification()->toArray();
		$return['default_configuration'] = $this->getDefaultConfiguration()->toArray();
		$return['name'] = $this->getName();
		$return['title'] = $this->getTitle();
		return $return;
	}
	
	/**
	 * Returns the data structure to serialize it as a json
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
	
}
