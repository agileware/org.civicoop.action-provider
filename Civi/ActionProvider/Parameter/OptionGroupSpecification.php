<?php

namespace Civi\ActionProvider\Parameter;

use CRM_ActionProvider_ExtensionUtil as E;

class OptionGroupSpecification extends Specification {
  
  protected $option_group_id;

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
  public function __construct($name, $option_group_id, $title='', $required = false, $defaultValue = null, $multiple = false) {
    $this->setName($name);
    $this->setDataType('String');
		$this->setTitle($title);
		$this->setRequired($required);
		$this->setDefaultValue($defaultValue);
		$this->setMultiple($multiple);
    $this->setOptionGroupId($option_group_id);    
  }
  
  /**
   * Set the option group id.
   * 
   * @param string
   * @return Specification
   */
  public function setOptionGroupId($option_group_id) {
    $this->option_group_id = $option_group_id;
    return $this;
  }
  
  public function getOptionGroupId() {
    return $this->option_group_id;
  }
	
	/**
   * @return array
   */
  public function getOptions() {
    $options = array();
    $optionsApi = civicrm_api3('OptionValue', 'get', array('option_group_id' => $this->option_group_id, 'options' => array('limit' => 0)));
    foreach($optionsApi['values'] as $optionValue) {
      $options[$optionValue['value']] = $optionValue['label'];
    }
    return $options;
  }
	
  /**
   * @param array $options
   *
   * @return $this
   */
  public function setOptions($options) {
    // Do nothing
    return $this;
  }
	
  /**
   * @param $option
   */
  public function addOption($option) {
    // Do nothing.
  }
	
}
