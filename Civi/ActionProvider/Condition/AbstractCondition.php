<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Condition;

use \Civi\ActionProvider\Provider;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use \Civi\ActionProvider\Parameter\SpecificationBag;

abstract class AbstractCondition implements \JsonSerializable {


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
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameterBag
   *
   * @return bool
   */
  abstract public function isConditionValid(ParameterBagInterface $parameterBag);

  /**
   * Returns the specification of the configuration options for the actual condition.
   *
   * @return SpecificationBag
   */
  abstract public function getConfigurationSpecification();

  /**
   * Returns the specification of the parameters of the actual condition.
   *
   * @return SpecificationBag
   */
  abstract public function getParameterSpecification();

  /**
   * Returns the human readable title of this condition
   */
  abstract public function getTitle();


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
   * Creates a parameterBag object.
   *
   * @return ParameterBagInterface
   */
  protected function createParameterBag() {
    return $this->provider->createParameterBag();
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
  protected function getMetaDataSpecification(): SpecificationBag {
    if ($this->provider) {
      return $this->provider->getMetaData()->getSpecificationBag();
    }
    return new SpecificationBag();
  }

  /**
   * @return \Civi\ActionProvider\Parameter\ParameterBag
   */
  protected function getMetaData(): ParameterBag {
    if ($this->provider) {
      return $this->provider->getMetaData()->getMetaData();
    }
    return new ParameterBag();
  }

  /**
   * Returns the system name of the action.
   *
   * We generate one based on the namespace of the class
   * and the class name.
   *
   * @return string
   * @throws \Exception
   */
  public function getName() {
    $reflect = new \ReflectionClass($this);
    $className = $reflect->getShortName();
    return $className;
  }

  /**
   * Converts the object to an array.
   *
   * @return array
   */
  public function toArray() {
    $return['parameter_spec'] = $this->getParameterSpecification()->toArray();
    $return['configuration_spec'] = $this->getConfigurationSpecification()->toArray();
    $return['default_configuration'] = null;
    if ($this->getDefaultConfiguration()) {
      $return['default_configuration'] = $this->getDefaultConfiguration()->toArray();
    }
    $return['name'] = $this->getName();
    $return['title'] = $this->getTitle();
    return $return;
  }

  /**
   * Returns the data structure to serialize it as a json
   */
  public function jsonSerialize(): array {
    $return['name'] = $this->getName();
    $return['title'] = $this->getTitle();
    // An empty array goes wrong with the default confifuration.
    if (empty($return['default_configuration'])) {
      $return['default_configuration'] = new \stdClass();;
    }
    return $return;
  }

}
