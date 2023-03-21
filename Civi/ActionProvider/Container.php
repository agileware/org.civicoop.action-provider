<?php

namespace Civi\ActionProvider;

use Civi\ActionProvider\Condition\AbstractCondition;

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
	public static function getInstance(): Container {
		if (!self::$instance) {
			self::$instance = new Container();
		}
		return self::$instance;
	}


	/**
	 * return Provider
	 */
	public function getDefaultProvider(): Provider {
		return $this->defaultProvider;
	}

	/**
	 * Returns the provider object the name of the context.
	 *
	 * @param string $context
	 * @return Provider
	 */
	public function getProviderByContext(string $context): Provider {
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
	public function hasContext(string $context): bool {
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
	public function addProviderWithContext(string $context, Provider $provider): Container {
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
  public function addAction(string $name, string $className, string $title, array $tags=[]): Container {
    $this->defaultProvider->addAction($name, $className, $title, $tags);
    foreach($this->providerContexts as $provider) {
      $provider->addAction($name, $className, $title, $tags);
    }
    return $this;
  }

  /**
   * Add an condition class to the list of available conditions.
   *
   * @param \Civi\ActionProvider\Condition\AbstractCondition $condition
   * @return Container
   * @throws \Exception
   */
  public function addCondition(AbstractCondition $condition): Container {
    $this->defaultProvider->addCondition($condition);
    foreach($this->providerContexts as $provider) {
      $provider->addCondition($condition);
    }
    return $this;
  }

  /**
   * @param string $name
   * @param string $className
   * @param string $title
   *
   * @return Container
   */
  public function addValidator(string $name, string $className, string $title): Container {
    $this->defaultProvider->addValidator($name, $className, $title);
    foreach($this->providerContexts as $provider) {
      $provider->addValidator($name, $className, $title);
    }
    return $this;
  }


}
