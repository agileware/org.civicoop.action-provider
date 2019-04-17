<?php

namespace Civi\ActionProvider\Utils\UserInterface;

use \Civi\ActionProvider\Action\AbstractAction;

use CRM_ActionProvider_ExtensionUtil as E;

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
   * @param $prefix
	 */
	public static function buildForm(\CRM_Core_Form $form, AbstractAction $action, $prefix=null) {
		// Check whether the actionProviderElementNames is already set if so get 
		// the variable and append the configuration for this action to it.
		// Because we dont want to overwrite the current actionProviderElementNames.	
		$elementNames = $form->get_template_vars('actionProviderElementNames');
		if (empty($elementNames)) {
			$elementNames = array();
		}
		if (!$prefix) {
		  $prefix = get_class($action);
    }
    $actionProviderElementDescriptions = array();
		$elementNames[$prefix] = array();
		foreach($action->getConfigurationSpecification() as $config_field) {
			$field_name = $prefix.$config_field->getName();
			if ($config_field->getDescription()) {
			  $actionProviderElementDescriptions[$field_name] = $config_field->getDescription();
      }
			$attributes = array(
				'class' => $prefix,
			);	
			
			if (!empty($config_field->getFkEntity())) {
				$attributes['entity'] = $config_field->getFkEntity();
        $attributes['placeholder'] = E::ts('- Select -');
        $attributes['select'] = array('minimumInputLength' => 0);
				if ($config_field->isMultiple()) {
					$attributes['multiple'] = true;
				}
				$form->addEntityRef($field_name, $config_field->getTitle(), $attributes, $config_field->isRequired());
				$elementNames[$prefix][] = $field_name;
			}else if (!empty($config_field->getOptions())) {
				$attributes['class'] .= ' crm-select2 huge';
				$attributes['placeholder'] = E::ts('- Select -');
				if ($config_field->isMultiple()) {
					$attributes['multiple'] = true;
					$options = $config_field->getOptions();
				} else {
					$options = $config_field->getOptions();
				}
				$form->add('select', $field_name, $config_field->getTitle(), $options, $config_field->isRequired(), $attributes);
				$elementNames[$prefix][] = $field_name;
			} else {
				$attributes['class'] .= ' huge';
				$form->add('text', $field_name, $config_field->getTitle(), $attributes, $config_field->isRequired());
				$elementNames[$prefix][] = $field_name;
			}

		}
		$form->assign('actionProviderElementNames', $elementNames);
		$form->assign('actionProviderElementDescriptions', $actionProviderElementDescriptions);
	}
	
	/**
	 * Returns the default values set by data or by the default value of the configuration specification.
	 * 
	 * @param AbtrsactAction $action
	 *   The action.
	 * @param array $data
	 *   The current configuration array
   * @param prefix
	 * @return array
	 */
	public static function setDefaultValues(AbstractAction $action, $data, $prefix=null) {
		$defaultValues = array();
    if (!$prefix) {
      $prefix = get_class($action);
    }
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
   * @param $prefix
	 * @return array
	 */
	public static function getSubmittedConfiguration(\CRM_Core_Form $form, AbstractAction $action, $prefix = null) {
    if (!$prefix) {
      $prefix = get_class($action);
    }
		$submitted_configuration = array();
		$submittedValues = $form->getVar('_submitValues');
		foreach($action->getConfigurationSpecification() as $config_field) {
  		if (isset($submittedValues[$prefix.$config_field->getName()])) {
  			$submitted_configuration[$config_field->getName()] = $submittedValues[$prefix.$config_field->getName()];
			}
		}
		return $submitted_configuration;
	}
		
}
