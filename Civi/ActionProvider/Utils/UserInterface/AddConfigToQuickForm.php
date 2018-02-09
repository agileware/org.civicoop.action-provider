<?php

namespace Civi\ActionProvider\Utils\UserInterface;

use \Civi\ActionProvider\Action\AbstractAction;

/**
 * Helper class to add a configuration specification on to a quick form class.
 */
class AddConfigToQuickForm {
	
	/**
	 * BuildForm helper. Adds the elements to the form.
	 *
	 * @param \CRM_Core_Form $form
	 *   The form.
	 * @param AbstractAction $action
	 *   The action
	 */
	public static function buildForm(\CRM_Core_Form $form, AbstractAction $action) {
		// Check whether the actionProviderElementNames is already set if so get 
		// the variable and append the configuration for this action to it.
		// Because we dont want to overwrite the current actionProviderElementNames.	
		$elementNames = $form->get_template_vars('actionProviderElementNames');
		if (empty($elementNames)) {
			$elementNames = array();
		}
		$prefix = $action->getName();
		$elementNames[$prefix] = array();
		foreach($action->getConfigurationSpecification() as $config_field) {
			$attributes = array(
				'class' => $prefix,
			);	
			
			if ($config_field->getDataType() == 'Integer') {
				$form->add('text', $prefix.$config_field->getName(), $config_field->getTitle(), $attributes, $config_field->isRequired());
				$elementNames[$prefix][] = $prefix.$config_field->getName();
			}
		}
		$form->assign('actionProviderElementNames', $elementNames);
	}
	
	/**
	 * Returns the default values set by data or by the default value of the configuration specification.
	 * 
	 * @param AbtrsactAction $action
	 *   The action.
	 * @param array $data
	 *   The current configuration array
	 * @return array
	 */
	public static function setDefaultValues(AbstractAction $action, $data) {
		$defaultValues = array();
		$prefix = $action->getName();
		foreach($action->getConfigurationSpecification() as $config_field) {
  		if (isset($data[$config_field->getName()])) {
  			$defaultValues[$prefix.$config_field->getName()] = $data[$config_field->getName()];
			} elseif (!empty($config_field->getDefaultValue())) {
				$defaultValues[$prefix.$config_field->getName()] = $config_field->getDefaultValue();
			}
		}
		return $defaultValues;
	}
	
	/**
	 * Returns the submitted configuration.
	 * 
	 * @param \CRM_Core_Form $form
	 *   The form.
	 * @param AbstractAction $action
	 *   The action
	 * @return array
	 */
	public static function getSubmittedConfiguration(\CRM_Core_Form $form, AbstractAction $action) {
		$prefix = $action->getName();	
		$submitted_configuration = array();
		$submittedValues = $form->get('_submitValues');
		foreach($action->getConfigurationSpecification() as $config_field) {
  		if (isset($submittedValues[$prefix.$config_field->getName()])) {
  			$submitted_configuration[$config_field->getName()] = $submittedValues[$prefix.$config_field->getName()];
			}
		}
		return $submitted_configuration;
	}
		
}
