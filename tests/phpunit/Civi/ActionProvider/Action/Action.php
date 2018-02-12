<?php

abstract class Civi_ActionProvider_Action_Action extends \PHPUnit_Framework_TestCase {
	
	/**
	 * @var \Civi\ActionProvider\Provider
	 */
	protected $provider;
	
	public function setup() {
		parent::setup();
		
		\Civi::reset();
		$civi_container = \Civi::container();
		$action_provider_container = $civi_container->get('action_provider');		
		$this->provider = $action_provider_container->getDefaultProvider();
	}
	
}
