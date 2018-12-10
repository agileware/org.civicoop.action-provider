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

  /**
   * @var array
   *   All the condition which are available to be used in this context.
   */
	protected $availableConditions = array();

  /**
   * @var array
   *   Contains all possible conditions.
   */
	protected $allConditions = array();
	
	public function __construct() {
		$actions = array(
		  new \Civi\ActionProvider\Action\Generic\SetValue(),
      new \Civi\ActionProvider\Action\Generic\SetDateValue(),
			new \Civi\ActionProvider\Action\Group\AddToGroup(),
      new \Civi\ActionProvider\Action\Group\AddToGroupParameter(),
      new \Civi\ActionProvider\Action\Group\RemoveFromGroupParameter(),
			new \Civi\ActionProvider\Action\Group\Create(),
      new \Civi\ActionProvider\Action\Group\GetGroup(),
      new \Civi\ActionProvider\Action\Group\DeleteGroup(),
			new \Civi\ActionProvider\Action\Contact\ContactDataById(),
			new \Civi\ActionProvider\Action\Contact\CreateUpdateAddress(),
			new \Civi\ActionProvider\Action\Contact\UsePrimaryAddressOfContact(),
			new \Civi\ActionProvider\Action\Contact\GetAddress(),
			new \Civi\ActionProvider\Action\Contact\GetContactIdFromMasterAddress(),
			new \Civi\ActionProvider\Action\Contact\CreateUpdateIndividual(),
      new \Civi\ActionProvider\Action\Contact\CreateUpdateHousehold(),
			new \Civi\ActionProvider\Action\Contact\UpdateCustomData(),
			new \Civi\ActionProvider\Action\Contact\FindOrCreateContactByEmail(),
			new \Civi\ActionProvider\Action\Activity\CreateActivity(),
      new \Civi\ActionProvider\Action\Activity\DeleteActivity(),
      new \Civi\ActionProvider\Action\Activity\GetActivity(),
			new \Civi\ActionProvider\Action\BulkMail\Send(),
			new \Civi\ActionProvider\Action\Event\UpdateParticipantStatus(),
      new \Civi\ActionProvider\Action\Event\UpdateParticipantStatusWithDynamicStatus(),
			new \Civi\ActionProvider\Action\Event\CreateOrUpdateParticipant(),
      new \Civi\ActionProvider\Action\Event\CreateOrUpdateParticipantWithDynamicStatus(),
      new \Civi\ActionProvider\Action\Event\CreateOrUpdateEvent(),
      new \Civi\ActionProvider\Action\Event\GetEvent(),
      new \Civi\ActionProvider\Action\Event\DeleteEvent(),
			new \Civi\ActionProvider\Action\Event\GetParticipant(),
      new \Civi\ActionProvider\Action\Event\DeleteParticipant(),
			new \Civi\ActionProvider\Action\Relationship\CreateRelationship(),
			new \Civi\ActionProvider\Action\Relationship\EndRelationship(),
			new \Civi\ActionProvider\Action\Website\CreateUpdateWebsite(),
			new \Civi\ActionProvider\Action\Website\GetWebsite(),
      new \Civi\ActionProvider\Action\Phone\CreateUpdatePhone(),
      new \Civi\ActionProvider\Action\Phone\GetPhone(),
      new \Civi\ActionProvider\Action\Membership\CreateOrUpdateMembership(),
      new \Civi\ActionProvider\Action\Membership\GetMembershipType(),
		);

		$conditions = array(
		  new \Civi\ActionProvider\Condition\ParameterIsEmpty(),
      new \Civi\ActionProvider\Condition\ParameterIsNotEmpty(),
      new \Civi\ActionProvider\Condition\ParameterHasValue(),
    );
		
		foreach($actions as $action) {
			$action->setProvider($this);
			$this->allActions[$action->getName()] = $action;
		}

    foreach($conditions as $condition) {
      $condition->setProvider($this);
      $this->allConditions[$condition->getName()] = $condition;
    }
		
		$this->availableActions = array_filter($this->allActions, array($this, 'filterActions'));
    $this->availableConditions = array_filter($this->allConditions, array($this, 'filterConditions'));
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
   * Returns all available conditins
   */
  public function getConditions() {
    return $this->availableConditions;
  }

  /**
   * Adds a condition to the list of available conditions.
   *
   * This function might be used by extensions to add their own conditions to the system.
   *
   * @param \Civi\ActionProvider\Condition\AbstractCondition $condition
   * @return Provider
   * @throws \Exception
   */
  public function addCondition(\Civi\ActionProvider\Condition\AbstractCondition $condition) {
    $condition->setProvider($this);
    $this->allConditions[$condition->getName()] = $condition;
    $this->availableConditions = array_filter($this->allConditions, array($this, 'filterConditions'));
    return $this;
  }

  /**
   * Returns a condition by its name.
   *
   * @return \Civi\ActionProvider\Condition\AbstractCondition|null when condition is not found.
   */
  public function getConditionByName($name) {
    if (isset($this->availableConditions[$name])) {
      $condition = clone $this->availableConditions[$name];
      $condition->setProvider($this);
      $condition->setDefaults();
      return $condition;
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

  /**
   * Filter the conditions array and keep certain condition.
   *
   * This function might be override in a child class to filter out certain conditions which do
   * not make sense in that context.
   *
   * @param \Civi\ActionProvider\Condition\AbstractCondition $condition
   *   The condition to filter.
   * @return bool
   *   Returns true when the element is valid, false when the element should be disregarded.
   */
  protected function filterConditions(\Civi\ActionProvider\Condition\AbstractCondition $condition) {
    return true;
  }
	
}
