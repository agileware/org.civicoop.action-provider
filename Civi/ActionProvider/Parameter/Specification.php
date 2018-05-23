<?php

namespace Civi\ActionProvider\Parameter;

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
   * @param string $name
   * @param string $dataType
   * @param string $title
   * @param bool $required
   * @param mixed $defaultValue
   * @param string|null $fkEntity
   * @param array $options
   * @param bool $multiple 
   */
  public function __construct($name, $dataType = 'String', $title='', $required = false, $defaultValue = null, $fkEntity = null, $options = array(), $multiple = false) {
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
    foreach (get_object_vars($this) as $key => $val) {
      $key = strtolower(preg_replace('/(?=[A-Z])/', '_$0', $key));
      $ret[$key] = $val;
    }
    return $ret;
  }
	
}
