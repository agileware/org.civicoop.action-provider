<?php

namespace Civi\ActionProvider\Parameter;

use CRM_ActionProvider_ExtensionUtil as E;

class OptionGroupByNameSpecification extends OptionGroupSpecification {
	
	/**
   * @return array
   */
  public function getOptions() {
    $options = array();
    $optionsApi = civicrm_api3('OptionValue', 'get', array('option_group_id' => $this->option_group_id, 'options' => array('limit' => 0)));
    foreach($optionsApi['values'] as $optionValue) {
      $options[$optionValue['name']] = $optionValue['label'];
    }
    return $options;
  }

}
