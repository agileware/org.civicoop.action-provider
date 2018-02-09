<?php

namespace Civi\ActionProvider;

use \Civi\ActionProvider\Action\AddToGroup;

/**
 * Singleton
 */
class Provider {
	
	protected $availableActions = array();
	
	private static $instance = null;
	
	private function __construct() {
		$actions = array(
			new AddToGroup(),
		);
		
		foreach($actions as $action) {
			$this->availableActions[$action->getName()] = $action;
		}
	}
	
	/**
	 * @return Provider
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Provider();
		}
		return self::$instance;
	}
	
	public function getActions() {
		return $this->availableActions;
	}
	
	public function getActionByName($name) {
		if (isset($this->availableActions[$name])) {
			return $this->availableActions[$name];
		}
		return null;
	}
	
}
