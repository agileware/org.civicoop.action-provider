<?php

namespace Civi\ActionProvider\Action;

use \Civi\ActionProvider\Provider;
use \Civi\ActionProvider\Condition\AbstractCondition;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\InvalidParameterException;
use \Civi\ActionProvider\Exception\InvalidConfigurationException;

/**
 * This is the abstract class for an action.
 *
 * Each action has a configuration which could be set in the user interface.
 * The parameters passed to the execute function are the data comming from the upper system such as the data in the trigger
 * with civirules. Or the data in the table with SqlTasks.
 *
 */
abstract class AbstractAction implements \JsonSerializable {

  // Use this tag for the action does not do any data manipulation
  // but only data retrieval.
  const DATA_RETRIEVAL_TAG = 'data-retrieval';

  // Use this tag if the action manipulates data.
	const DATA_MANIPULATION_TAG = 'data-manipulation';

  // Use this tag if the action works with a single contact.
	const SINGLE_CONTACT_ACTION_TAG = 'act-on-a-single-contact';

  // Use this tag if the action works with multieple contacts.
	const MULTIPLE_CONTACTS_ACTION_TAG = 'action-on-multiple-contacts';

  // Use this tag if the action works without a contact.
  const WITHOUT_CONTACT_ACTION_TAG = 'act-without-a-contact';

	const SEND_MESSAGES_TO_CONTACTS = 'send-messages';

	/**
	 * @var ParameterBag
	 */
	protected $configuration;

	/**
	 * @var ParameterBag
	 */
	protected $defaultConfiguration;

	/**
	 * @var Provider
	 */
	protected $provider;

  /**
   * @var name of the current batcj
   */
	protected $currentBatch;

  /**
   * @var AbstractCondition
   */
  private $condition;

  // Reference counter for adding and removing API Wrapper when it's needed
  protected static $wrapperLevel = 0;

	public function __construct() {

	}

	/**
	 * Run the action
	 *
	 * @param ParameterBagInterface $parameters
	 *   The parameters to this action.
	 * @param ParameterBagInterface $output
	 * 	 The parameters this action can send back
	 * @return void
	 */
	abstract protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output);

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	abstract public function getConfigurationSpecification();

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	abstract public function getParameterSpecification();

	/**
	 * Returns the specification of the output parameters of this action.
	 *
	 * This function could be overridden by child classes.
	 *
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		return new SpecificationBag();
	}

	/**
	 * Execute the action.
	 *
	 * The execute method will first validate the given configuration and the given
	 * parameters against their specifcation.
	 *
	 * After that it will fire the doAction method which is implemented in a child class to do the
	 * actual action.
	 * This method is basicly a wrapper around doAction.
	 *
	 * @param ParameterBagInterface $parameters;
   * @param ParameterBagInterface $conditionParameters
   * @param ParameterBagInterface $invalidConditionOutput
   * @return ParameterBagInterface
   * @throws \Exception
	 */
	public function execute(ParameterBagInterface $parameters, ParameterBagInterface $conditionParameters, ParameterBagInterface $invalidConditionOutput) {
		if ($this->condition && !$this->condition->isConditionValid($conditionParameters)) {
      // Condition is invalid
      return $invalidConditionOutput;
    }

    try {
      $this->validateConfiguration();
    } catch (InvalidParameterException $e) {
      throw new InvalidConfigurationException("Found invalid configuration for the action: ".get_class($this).' '.$e->getMessage());
    }
    try {
      $this->validateParameters($parameters);
    } catch (InvalidParameterException $e) {
      throw new InvalidParameterException("Found invalid parameters for the action: ".get_class($this).' '.$e->getMessage());
    }

    // Condition is valid or no condition class is set
    $output = $this->createParameterBag();
    try {
      // Install API wrapper at first level of action provider only
      if (++ self::$wrapperLevel == 1) {
        \Civi::dispatcher()->addListener('civi.api.prepare', ['Civi\ActionProvider\APIWrapper', 'onApiPrepare'], -100);
      }
      $this->doAction($parameters, $output);
    } finally {
      // Uninstall API wrapper, even if there was an exception
      if (-- self::$wrapperLevel == 0) {
        \Civi::dispatcher()->removeListener('civi.api.prepare', ['Civi\ActionProvider\APIWrapper', 'onApiPrepare']);
      }
    }
		return $output;
	}

  /**
   * This function initialize a batch.
   *
   * @param $batchName
   */
	public function initializeBatch($batchName) {
    $this->currentBatch = $batchName;
  }

  /**
   * This function finishes a batch and is called when a batch with actions is finished.
   *
   * @param $batchName
   * @param bool
   *   Whether this was the last batch.
   */
  public function finishBatch($batchName, $isLastBatch=false) {
    // Child classes could override this function
    // E.g. merge files in a directory
    $this->currentBatch = null;
  }



	/**
	 * @return bool
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
	 */
	protected function validateParameters(ParameterBagInterface $parameters) {
		return SpecificationBag::validate($parameters, $this->getParameterSpecification());
	}

	/**
	 * @return bool;
	 */
	protected function validateConfiguration() {
		if ($this->configuration === null) {
			return false;
		}
		return SpecificationBag::validate($this->configuration, $this->getConfigurationSpecification());
	}

	/**
	 * @return ParameterBag
	 */
	public function getDefaultConfiguration() {
		return $this->defaultConfiguration;
	}

	/**
	 * @return ParameterBag
	 */
	public function getConfiguration() {
		return $this->configuration;
	}

	/**
	 * @param ParameterBag $configuration
   * @return AbstractAction
	 */
	public function setConfiguration(ParameterBag $configuration) {
		$this->configuration = $configuration;
		return $this;
	}

  /**
   * Sets the condition object
   *
   * @param AbstractCondition|null $condition
   * @return AbstractAction
   */
	public function setCondition(AbstractCondition $condition=null) {
	  $this->condition = $condition;
	  return $this;
  }

	/**
	 * Sets the provider class
	 *
   * @param Provider $provider
	 * @return AbstractAction
	 */
	public function setProvider(Provider $provider) {
		$this->provider = $provider;
		return $this;
	}

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag|null
   */
  protected function getMetadataSpecification(): SpecificationBag {
    if ($this->provider) {
      return $this->provider->getMetaData()->getSpecificationBag();
    }
    return new SpecificationBag();
  }

  /**
   * @return \Civi\ActionProvider\Parameter\ParameterBag
   */
  protected function getMetadata(): ParameterBag {
    if ($this->provider) {
      return $this->provider->getMetaData()->getMetaData();
    }
    return new ParameterBag();
  }

	/**
	 * Sets the default values of this action
	 */
	public function setDefaults() {
		$this->configuration = $this->createParameterBag();
		$this->defaultConfiguration = $this->createParameterBag();

		foreach($this->getConfigurationSpecification() as $spec) {
			if ($spec->getDefaultValue() !== null) {
				$this->configuration->setParameter($spec->getName(), $spec->getDefaultValue());
				$this->defaultConfiguration->setParameter($spec->getName(), $spec->getDefaultValue());
			}
		}
	}

  /**
   * Returns a help text for this action.
   *
   * The help text is shown to the administrator who is configuring the action.
   * Override this function in a child class if your action has a help text.
   *
   * @return string|false
   */
	public function getHelpText() {
	  return false;
  }

	/**
	 * Creates a parameterBag object.
	 *
	 * @return ParameterBagInterface
	 */
	protected function createParameterBag() {
		return $this->provider->createParameterBag();
	}

	/**
	 * Converts the object to an array.
	 *
	 * @return array
	 */
	public function toArray() {
		$return['parameter_spec'] = $this->getParameterSpecification()->toArray();
		$return['configuration_spec'] = $this->getConfigurationSpecification()->toArray();
		$return['output_spec'] = $this->getOutputSpecification()->toArray();
    $return['help_text'] = $this->getHelpText();
		$return['default_configuration'] = null;
		if ($this->getDefaultConfiguration()) {
			$return['default_configuration'] = $this->getDefaultConfiguration()->toArray();
		}
		return $return;
	}

	/**
	 * Returns the data structure to serialize it as a json
	 */
	public function jsonSerialize(): array {
		// An empty array goes wrong with the default confifuration.
    $return = [];
		if (empty($return['default_configuration'])) {
			$return['default_configuration'] = new \stdClass();;
		}
		return $return;
	}

}
