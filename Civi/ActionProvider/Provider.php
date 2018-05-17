<?php

namespace Civi\ActionProvider;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;

/**
 * Singleton and conatiner class with all the actions.
 * 
 * This class could be overriden by child classes in an extension to provide a context aware container 
 * for the actions. 
 */
class Provider {
	
	/**
	 * @var array
	 *   All the actions which are available for use in this context.
	 */
	protected $availableActions = array();
	
	/**
	 * @var array
	 *   All the actions including the inactive ones.
	 */
	protected $allActions = array();
	
	public function __construct() {
		$actions = array(
			new \Civi\ActionProvider\Action\AddToGroup(),
			new \Civi\ActionProvider\Action\Contact\ContactDataById(),
			new \Civi\ActionProvider\Action\Contact\FindOrCreateContactByEmail(),
			new \Civi\ActionProvider\Action\Event\UpdateParticipantStatus(),
		);
		
		foreach($actions as $action) {
			$action->setProvider($this);
			$this->allActions[$action->getName()] = $action;
		}
		
		$this->availableActions = array_filter($this->allActions, array($this, 'filterActions'));
	}
	
	/**
	 * Returns all available actions
	 */
	public function getActions() {
		return $this->availableActions;
	}
	
	/**
	 * Adds an action to the list of available actions.
	 * 
	 * This function might be used by extensions to add their own actions to the system.
	 * 
	 * @param \Civi\ActionProvider\Action\AbstractAction $action
	 * @return Provider
	 */
	public function addAction(\Civi\ActionProvider\Action\AbstractAction $action) {
		$action->setProvider($this);
		$this->allActions[$action->getName()] = $action;
		$this->availableActions = array_filter($this->allActions, array($this, 'filterActions'));
		return $this;
	}
	
	/**
	 * Returns an action by its name.
	 * 
	 * @return \Civi\ActionProvider\Action\AbstractAction|null when action is not found.
	 */
	public function getActionByName($name) {
		if (isset($this->availableActions[$name])) {
			$action = clone $this->availableActions[$name];
			$action->setProvider($this);
			$action->setDefaults();
			return $action;
		}
		return null;
	}
	
	/**
	 * Returns a new ParameterBag
	 * 
	 * This function exists so we can encapsulate the creation of a ParameterBag to the provider.
	 * 
	 * @return ParameterBagInterface
	 */
	public function createParameterBag() {
		return new ParameterBag();
	}
	
	/**
	 * Returns a new parameter bag based on the given mapping.
	 * 
	 * @param ParameterBagInterface $parameterBag
	 * @param array $mapping
	 * @return ParameterBagInterface
	 */
	public function createdMappedParameterBag(ParameterBagInterface $parameterBag, $mapping) {
		$mappedParameterBag = $this->createParameterBag();
		foreach($mapping as $mappedField => $field) {
			if ($parameterBag->doesParameterExists($field)) {
				$mappedParameterBag->setParameter($mappedField, $parameterBag->getParameter($field));
			}
		}
		return $mappedParameterBag;
	}
	
	/**
	 * Filter the actions array and keep certain actions.
	 * 
	 * This function might be override in a child class to filter out certain actions which do
	 * not make sense in that context. E.g. for example CiviRules has already a AddContactToGroup action 
	 * so it does not make sense to use the one provided by us.
	 * 
	 * @param \Civi\ActionProvider\Action\AbstractAction $action
	 *   The action to filter.
	 * @return bool
	 *   Returns true when the element is valid, false when the element should be disregarded.
	 */
	protected function filterActions(\Civi\ActionProvider\Action\AbstractAction $action) {
		return true;
	}
	
}
