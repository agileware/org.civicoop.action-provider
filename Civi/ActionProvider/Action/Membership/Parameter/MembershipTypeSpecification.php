<?php

namespace Civi\ActionProvider\Action\Membership\Parameter;

use CRM_ActionProvider_ExtensionUtil as E;

use \Civi\ActionProvider\Parameter\Specification;

class MembershipTypeSpecification extends Specification {

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
  public function __construct($name, $title='', $required = false, $defaultValue = null, $multiple = false) {
    $this->setName($name);
    $this->setDataType('String');
		$this->setTitle($title);
		$this->setRequired($required);
		$this->setDefaultValue($defaultValue);
		$this->setMultiple($multiple);
  }
	
	/**
   * @return array
   */
  public function getOptions() {
    $options = array();
    $optionsApi = civicrm_api3('MembershipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    foreach($optionsApi['values'] as $optionValue) {
      $options[$optionValue['id']] = $optionValue['name'];
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
