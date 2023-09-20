<?php

namespace Civi\ActionProvider;

use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Validation\Validators;
use Civi\ActionProvider\Event\InitializeMetaDataEvent;

/**
 * Singleton and conatiner class with all the actions.
 *
 * This class could be overridden by child classes in an extension to provide a context aware container
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
   */
	protected $actionTitles = array();

  /**
   * @var array
   */
  protected $acttionTags = array();

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

  /**
   * @var AbstractAction[]
   *   Contains all instanciated actions.
   */
	protected $batchActions = array();

  /**
   * @var array
   */
  protected $allValidators = array();

  /**
   * @var array
   */
  protected $validatorTitles = array();

  /** @var \Civi\ActionProvider\MetaData */
  protected $metaData = null;

	public function __construct() {
    Action\Contact\Actions::loadActions($this);
    Action\Group\Actions::loadActions($this);
    Action\Relationship\Actions::loadActions($this);
    Action\Activity\Actions::loadActions($this);
    Action\Contribution\Actions::loadActions($this);
    Action\Event\Actions::loadActions($this);
    Action\CiviCase\Actions::loadActions($this);
    Action\Membership\Actions::loadActions($this);
    Action\Campaign\Actions::loadActions($this);
    Action\BulkMail\Actions::loadActions($this);
    Action\MailingEvent\Actions::loadActions($this);
    Action\Communication\Actions::loadActions($this);
    Action\Generic\Actions::loadActions($this);
    Action\Tag\Actions::loadActions($this);
    Action\SMS\Actions::loadActions($this);

		$conditions = array(
		  new \Civi\ActionProvider\Condition\ParameterIsEmpty(),
      new \Civi\ActionProvider\Condition\ParameterIsNotEmpty(),
      new \Civi\ActionProvider\Condition\ParameterHasValue(),
      new \Civi\ActionProvider\Condition\ParameterIsNot(),
      new \Civi\ActionProvider\Condition\CompareParameterValue(),
      new \Civi\ActionProvider\Condition\CompareParameterRegex(),
      new \Civi\ActionProvider\Condition\ParametersMatch(),
      new \Civi\ActionProvider\Condition\ParametersDontMatch(),
      new \Civi\ActionProvider\Condition\CheckParameters(),
      new \Civi\ActionProvider\Condition\ParametersAreEqual(),
      new \Civi\ActionProvider\Condition\ArrayParameterContains(),
      new \Civi\ActionProvider\Condition\ContactHasSubtype(),
      new \Civi\ActionProvider\Condition\ContactHasTag(),
      new \Civi\ActionProvider\Condition\ContactHasActivity(),
    );

    foreach($conditions as $condition) {
      $condition->setProvider($this);
      $this->allConditions[$condition->getName()] = $condition;
    }
    $this->availableConditions = array_filter($this->allConditions, array($this, 'filterConditions'));

    Validators::loadValidators($this);

    $this->initializeMetaData();
	}

  public function getMetaData(): MetaData {
    return $this->metaData;
  }

	/**
	 * Returns all available actions
	 */
	public function getActions() {
		return $this->availableActions;
	}

	public function getActionTitles() {
	  $titles = array();
	  foreach($this->availableActions as $actionName => $actionClass) {
	    if (isset($this->actionTitles[$actionName])) {
        $titles[$actionName] = $this->actionTitles[$actionName];
      }
    }
	  asort($titles);

    return $titles;
  }

	/**
	 * Adds an action to the list of available actions.
	 *
	 * This function might be used by extensions to add their own actions to the system.
	 *
	 * @param String $name
   * @param String $className
   * @param String $title
   * @param String[] $tags
	 * @return Provider
	 */
	public function addAction($name, $className, $title, $tags=array()) {
		$this->addActionWithoutFiltering($name, $className, $title, $tags);
		$this->availableActions = array_filter($this->allActions, array($this, 'filterActions'));
		return $this;
	}

  /**
   * Adds an action to the list of available actions.
   *
   * This function might be used by extensions to add their own actions to the system.
   *
   * @param String $name
   * @param String $className
   * @param String $title
   * @param String[] $tags
   * @return Provider
   */
  private function addActionWithoutFiltering($name, $className, $title, $tags=array()) {
    $this->allActions[$name] = $className;
    $this->actionTitles[$name] = $title;
    $this->acttionTags[$name] = $tags;
    return $this;
  }

	/**
	 * Returns an action by its name.
	 *
	 * @return \Civi\ActionProvider\Action\AbstractAction|null when action is not found.
	 */
	public function getActionByName($name) {
		if (isset($this->availableActions[$name])) {
			$action = new $this->availableActions[$name];
			$action->setProvider($this);
			$action->setDefaults();
			return $action;
		}
		return null;
	}

  /**
   * @param string $name
   * @param string $className
   * @param string $title
   *
   * @return $this
   */
  public function addValidator($name, $className, $title): Provider {
    $this->allValidators[$name] = $className;
    $this->validatorTitles[$name] = $title;
    return $this;
  }

  /**
   * @param string $name
   *
   * @return \Civi\ActionProvider\Validation\AbstractValidator|null
   */
  public function getValidatorByName($name) {
    if (isset($this->allValidators[$name])) {
      $validator = new $this->allValidators[$name];
      $validator->setProvider($this);
      $validator->setDefaults();
      return $validator;
    }
    return null;
  }

  /**
   * @return array
   */
  public function getValidatorTitles(): array {
    return $this->validatorTitles;
  }

  /**
   * @return array
   */
  public function getValidators(): array {
    return $this->allValidators;
  }

  /**
   * Returns an action and store the instance to use in batch mode
   *
   * @return \Civi\ActionProvider\Action\AbstractAction|null when action is not found.
   */
  public function getBatchActionByName($name, $configuration, $batchName) {
    if (!isset($this->batchActions[$batchName])) {
      $this->batchActions[$batchName] = array();
    }
    if (!isset($this->batchActions[$batchName][$name])) {
      $this->batchActions[$batchName][$name] = $this->getActionByName($name);
      if (!$this->batchActions[$batchName][$name]) {
        return null;
      }
      $this->batchActions[$batchName][$name]->getConfiguration()->fromArray($configuration);
      $this->batchActions[$batchName][$name]->initializeBatch($batchName);
    } else {
      $this->batchActions[$batchName][$name]->getConfiguration()->fromArray($configuration);
    }
    return $this->batchActions[$batchName][$name];
  }

  /**
   * Finish a batch
   *
   * @param $batchName
   * @param bool $isLastBatch
   */
  public function finishBatch($batchName, $isLastBatch=false) {
    if (isset($this->batchActions[$batchName])) {
      foreach($this->batchActions[$batchName] as $actionName => $action) {
        $action->finishBatch($batchName, $isLastBatch);
        unset($this->batchActions[$batchName][$actionName]);
      }
    }
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
		  if (is_array($field)) {
        $subParameterBags = array();
		    foreach($field as $subField) {
		      if (isset($subField['parameter_mapping'])) {
            $subParameterBags[] = $this->createdMappedParameterBag($parameterBag, $subField['parameter_mapping']);
          } elseif ($parameterBag->doesParameterExists($subField)) {
		        $parameter = $parameterBag->getParameter($subField);
		        if(is_array($parameter)) {
		          // flatten the array.
              $subParameterBags = array_merge($subParameterBags,$parameterBag->getParameter($subField));
            } else {
              $subParameterBags[] = $parameterBag->getParameter($subField);
            }
          }
        }
		    $mappedParameterBag->setParameter($mappedField, $subParameterBags);
      } elseif ($parameterBag->doesParameterExists($field)) {
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
	 * @param string
	 *   The action to filter.
	 * @return bool
	 *   Returns true when the element is valid, false when the element should be disregarded.
	 */
	protected function filterActions($actionName) {
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

  /**
   * Initialize the meta data
   *
   * @return void
   */
  protected function initializeMetaData() {
    $this->metaData = new MetaData();
    $event = new InitializeMetaDataEvent($this->metaData);
    \Civi::dispatcher()->dispatch(InitializeMetaDataEvent::EVENT_NAME, $event);
  }

}
