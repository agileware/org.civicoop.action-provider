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
		$this->defaultProvider = new Provider();
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
			return $this->providerContexts[$context];
		}
		return $this->defaultProvider;
	}
	
	/**
	 * Returns whether the container has already a particulair context.
	 * 
	 * @param string $context
	 * @return bool
	 */
	public function hasContext($context) {
		if (isset($this->providerContexts[$context])) {
			return true;
		}
		return false;
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
	public function addProviderWithContext($context, Provider $provider) {
		$this->providerContexts[$context] = $provider;
		return $this;
	}

  /**
   * Adds an action to the list of available actions.
   *
   * @param String $name
   * @param String $className
   * @param String $title
   * @param String[] $tags
   * @return Container
   */
  public function addAction($name, $className, $title, $tags=array()) {
    $this->defaultProvider->addAction($name, $className, $title, $tags);
    foreach($this->providerContexts as $provider) {
      $provider->addAction($name, $className, $title, $tags);
    }
    return $this;
  }
	
	
}
