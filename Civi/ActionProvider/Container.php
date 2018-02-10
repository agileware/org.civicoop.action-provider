<?php

namespace Civi\ActionProvider;

use \Civi\ActionProvider\Provider;

class Container {
	
	/**
	 * @var Provider
	 */
	protected $defaultProvider;
	
	protected $providerContexts = array();
	
	private static $instance;
	
	protected function __construct() {
		$this->defaultProvider = Provider::getInstance();
	}
	
	/**
	 * @return Container
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new Container();
		}
		return self::$instance;
	}
	
	
	/**
	 * return Provider
	 */
	public function getDefaultProvider() {
		return $this->defaultProvider;
	}
	
	/**
	 * Returns the provider object the name of the context.
	 * 
	 * @param string $context
	 * @return Provider
	 */
	public function getProviderByContext($context) {
		if (isset($this->providerContexts[$context])) {
			return $this->providerContexts;
		}
		return $this->defaultProvider;
	}
	
	/**
	 * Adds a Provider for a certain context. 
	 * 
	 * A context could be the name of the extension etc...	 * 
	 * The name of the context is defined by other parts of the system.
	 * This way one add a specific context from with an extenion.
	 * 
	 * @param string $context
	 * @param Provider $provider
	 * @return Container
	 */
	public function addContextProvider($context, Provider $provider) {
		$this->providerContexts[$context] = $provider;
		return $this;
	}
	
	
}
