<?php

namespace Civi\ActionProvider;

use \Civi\ActionProvider\Action\AddToGroup;
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
			new AddToGroup(),
		);
		
		foreach($actions as $action) {
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
			return $this->availableActions[$name];
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
