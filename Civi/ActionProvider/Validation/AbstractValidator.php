<?php
/**
 * Copyright (C) 2023  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Civi\ActionProvider\Validation;

use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Provider;
use JsonSerializable;
use stdClass;

abstract class AbstractValidator implements JsonSerializable {

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
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  abstract public function getConfigurationSpecification(): SpecificationBag;

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  abstract public function getParameterSpecification(): SpecificationBag;

  /**
   * Returns null when valid. When invalid return a string containing an explanation message.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return string|null
   */
  abstract protected function doValidation(ParameterBagInterface $parameters):? string;

  /**
   * Validate
   *
   * @param ParameterBagInterface $parameters;
   * @return string|null
   */
  public function validate(ParameterBagInterface $parameters):? string {
    try {
      $this->validateConfiguration();
    } catch (InvalidParameterException $e) {
      return "Found invalid configuration for the action: ".get_class($this).' '.$e->getMessage();
    }
    try {
      $this->validateParameters($parameters);
    } catch (InvalidParameterException $e) {
      return "Found invalid parameters for the action: ".get_class($this).' '.$e->getMessage();
    }

    return $this->doValidation($parameters);
  }

  /**
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return bool
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  protected function validateParameters(ParameterBagInterface $parameters): bool {
    return SpecificationBag::validate($parameters, $this->getParameterSpecification());
  }

  /**
   * @return bool;
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  protected function validateConfiguration(): bool {
    if ($this->configuration === null) {
      return false;
    }
    return SpecificationBag::validate($this->configuration, $this->getConfigurationSpecification());
  }

  /**
   * @return ParameterBag
   */
  public function getDefaultConfiguration(): ParameterBag {
    return $this->defaultConfiguration;
  }

  /**
   * @return ParameterBag
   */
  public function getConfiguration(): ParameterBag {
    return $this->configuration;
  }

  /**
   * @param ParameterBag $configuration
   * @return \Civi\ActionProvider\Validation\AbstractValidator
   */
  public function setConfiguration(ParameterBag $configuration): AbstractValidator {
    $this->configuration = $configuration;
    return $this;
  }

  /**
   * Sets the provider class
   *
   * @param Provider $provider
   * @return \Civi\ActionProvider\Validation\AbstractValidator
   */
  public function setProvider(Provider $provider): AbstractValidator {
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
  public function getHelpText():? string {
    return false;
  }

  /**
   * Creates a parameterBag object.
   *
   * @return ParameterBagInterface
   */
  protected function createParameterBag(): ParameterBagInterface {
    return $this->provider->createParameterBag();
  }

  /**
   * Converts the object to an array.
   *
   * @return array
   */
  public function toArray(): array {
    $return['parameter_spec'] = $this->getParameterSpecification()->toArray();
    $return['configuration_spec'] = $this->getConfigurationSpecification()->toArray();
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
    $return = $this->toArray();
    if (empty($return['default_configuration'])) {
      $return['default_configuration'] = new stdClass();
    }
    return $return;
  }

}
