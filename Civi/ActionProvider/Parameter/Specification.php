<?php

namespace Civi\ActionProvider\Parameter;

use Civi\ActionProvider\Exception\InvalidParameterException;
use CRM_ActionProvider_ExtensionUtil as E;

class Specification {
	
	 /**
   * @var mixed
   */
  protected $defaultValue;
  /**
   * @var string
   */
  protected $name;
  /**
   * @var string
   */
  protected $title;
  /**
   * @var string
   */
  protected $description;
  /**
   * @var bool
   */
  protected $required = FALSE;
  /**
   * @var array
   */
  protected $options = array();
	/**
	 * @var bool
	 */
	protected $multiple = FALSE;
  /**
   * @var string
   */
  protected $dataType;
  /**
   * @var string
   */
  protected $fkEntity;
  
  /**
   * @var string
   */
  protected $apiFieldName;
	
  /**
   * @param string $name
   * @param string $dataType
   * @param string $title
   * @param bool $required
   * @param mixed $defaultValue
   * @param string|null $fkEntity
   * @param array $options
   * @param bool $multiple 
   */
  public function __construct($name, $dataType = 'String', $title='', $required = false, $defaultValue = null, $fkEntity = null, $options = null, $multiple = false) {
    $this->setName($name);
    $this->setDataType($dataType);
		$this->setTitle($title);
		$this->setRequired($required);
		$this->setDefaultValue($defaultValue);
		$this->setFkEntity($fkEntity);
		$this->setOptions($options);
		$this->setMultiple($multiple);
    
    if ($this->dataType == 'Boolean') {
      $this->options = array(
        '0' => E::ts('No'),
        '1' => E::ts('Yes'),
      );
    }
  }
	
  /**
   * @return mixed
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }
	
  /**
   * @param mixed $defaultValue
   *
   * @return $this
   */
  public function setDefaultValue($defaultValue) {
    $this->defaultValue = $defaultValue;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
	
  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
  
  /**
   * @return string
   */
  public function getApiFieldName() {
    return $this->apiFieldName;
  }
  
  /**
   * @param string $name
   *
   * @return $this
   */
  public function setApiFieldName($name) {
    $this->apiFieldName = $name;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }
	
  /**
   * @param string $title
   *
   * @return $this
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }
	
  /**
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }
	
  /**
   * @return bool
   */
  public function isRequired() {
    return $this->required;
  }
	
  /**
   * @param bool $required
   *
   * @return $this
   */
  public function setRequired($required) {
    $this->required = $required;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getDataType() {
    return $this->dataType;
  }
	
  /**
   * @param $dataType
   *
   * @return $this
   * @throws \Exception
   */
  public function setDataType($dataType) {
    if (!in_array($dataType, $this->getValidDataTypes())) {
      throw new \Exception(sprintf('Invalid data type "%s', $dataType));
    }
    $this->dataType = $dataType;
    return $this;
  }
	
	  /**
   * Add valid types that are not not part of \CRM_Utils_Type::dataTypes
   *
   * @return array
   */
  private function getValidDataTypes() {
    $extraTypes =  array('Boolean', 'Text', 'Float');
    $extraTypes = array_combine($extraTypes, $extraTypes);
    return array_merge(\CRM_Utils_Type::dataTypes(), $extraTypes);
  }
	
	 /**
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }
	
  /**
   * @param array $options
   *
   * @return $this
   */
  public function setOptions($options) {
    $this->options = $options;
    return $this;
  }
	
  /**
   * @param $option
   */
  public function addOption($option) {
    $this->options[] = $option;
  }
	
	/**
   * @return bool
   */
  public function isMultiple() {
    return $this->multiple;
  }
	
  /**
   * @param bool $multiple
   *
   * @return $this
   */
  public function setMultiple($multiple) {
    $this->multiple = $multiple;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getFkEntity() {
    return $this->fkEntity;
  }
	
  /**
   * @param string $fkEntity
   *
   * @return $this
   */
  public function setFkEntity($fkEntity) {
    $this->fkEntity = $fkEntity;
    return $this;
  }
	
	public function toArray() {
    $ret = array();
    foreach (get_class_methods($this) as $method) {
      $var = false;
      if (stripos($method, 'get') === 0) {
        $var = lcfirst(substr($method, 3));
      } elseif (stripos($method, 'is') === 0) {
        $var = lcfirst(substr($method, 2));
      }
      if (!$var) {
        continue;
      }
      
      $key = strtolower(preg_replace('/(?=[A-Z])/', '_$0', $var));
      $ret[$key] = $this->$method();
    }
    return $ret;
  }

  /**
   * Validate a value
   *
   * @param $value
   *
   * @throws \CRM_Core_Exception
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  public function validate($value) {
    if ($this->isMultiple()) {
      if (is_array($value)) {
        foreach ($value as $v) {
          if ($v && \CRM_Utils_Type::validate($v, $this->getDataType(), FALSE) === NULL) {
            throw new InvalidParameterException($this->getName(). ' is invalid');
          }
        }
      } else {
        if ($value && \CRM_Utils_Type::validate($value, $this->getDataType(), FALSE) === NULL) {
          throw new InvalidParameterException($this->getName(). ' is invalid');
        }
      }
    } else {
      if (is_array($value)) {
        throw new InvalidParameterException($this->getName(). ' requires a single value a multiple value is given');
      }
      if ($value && \CRM_Utils_Type::validate($value, $this->getDataType(), FALSE) === NULL) {
        throw new InvalidParameterException($this->getName(). ' is invalid');
      }
    }
  }
	
}
