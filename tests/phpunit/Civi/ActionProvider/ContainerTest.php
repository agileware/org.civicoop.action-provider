<?php

use CRM_ActionProvider_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class Civi_ActionProvider_ContainerTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test whether the container is created 
	 * and whether the default functions work as exepcted
	 * 
   */
  public function testContainer() {
  	\Civi::reset(); // Reset the civi container so its rebuild again
  	$civi_container = \Civi::container();
		$action_provider_container = $civi_container->get('action_provider');
		$this->assertInstanceOf('\Civi\ActionProvider\Container', $action_provider_container, 'The container is not created successfully');
		
		$defaultProvider = $action_provider_container->getDefaultProvider();
		$this->assertInstanceOf('\Civi\ActionProvider\Provider', $defaultProvider, 'Default provider is not set correctly');
		$action_provider_container->addProviderWithContext('unittest', new \Civi\ActionProvider\Provider());
		$contextProvider = $action_provider_container->getProviderByContext('unittest');
		$this->assertNotSame($defaultProvider, $contextProvider, 'Context provider should differ from the default provider');
		$this->assertInstanceOf('\Civi\ActionProvider\Provider', $contextProvider, 'Context provider is not set correctly');
  }

}
