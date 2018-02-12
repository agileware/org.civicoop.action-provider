<?php

require_once(__DIR__.'/Action.php');

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
class Civi_ActionProvider_Action_AddToGroupTest extends \Civi_ActionProvider_Action_Action implements HeadlessInterface, TransactionalInterface {

	protected $contactId;
	
	protected $groupId;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
  	$result = civicrm_api3("Contact","create",array(
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name'  => 'Jensen'
    ));
    $this->contactId=$result['id'];

    $result = civicrm_api3('Group','create',array(
      'title' => "TestGroup",
      'name' => "test_group",
    ));
    $this->groupId = $result['id'];
		
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * First test invalid configuration, then a valid one 
	 * and then check whether the contact is added to the group.	 * 
   */
  public function testAction() {
  	$action = $this->provider->getActionByName('AddToGroup');
		
		try {
			$action->execute($this->provider->createParameterBag());
		} catch (Exception $e) {
			$this->assertInstanceOf('\Civi\ActionProvider\Exception\InvalidConfigurationException', $e, 'Wrong exception thrown');
		}
		
		// Now set a valid configuration
		$config = $this->provider->createParameterBag();
		$config->setParameter('group_id', $this->groupId);
		$action->setConfiguration($config);
		try {
			$action->execute($this->provider->createParameterBag());
		} catch (Exception $e) {
			$this->assertInstanceOf('\Civi\ActionProvider\Exception\InvalidParameterException', $e, 'Wrong exception thrown');
		}
	}
}

?>