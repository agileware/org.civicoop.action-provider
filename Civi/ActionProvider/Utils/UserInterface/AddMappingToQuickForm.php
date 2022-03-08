<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils\UserInterface;

use CRM_ActionProvider_ExtensionUtil as E;

class AddMappingToQuickForm {

  public static function addMapping($prefix, \Civi\ActionProvider\Parameter\SpecificationBag $parameterSpecs, $current_mapping, \CRM_Core_Form $form, $availableFields) {
    $actionProviderMappingFields = $form->get_template_vars('actionProviderMappingFields') ? $form->get_template_vars('actionProviderMappingFields') : array();
    $actionProviderMappingDescriptions = $form->get_template_vars('actionProviderMappingDescriptions') ? $form->get_template_vars('actionProviderMappingFields') : array();
    $actionProviderGroupedMappingFields = $form->get_template_vars('actionProviderGroupedMappingFields') ? $form->get_template_vars('actionProviderMappingFields') : array();
    foreach($parameterSpecs as $spec) {
      if ($spec instanceof \Civi\ActionProvider\Parameter\SpecificationGroup) {
        $actionProviderGroupedMappingFields[$prefix][$spec->getName()]['title'] = $spec->getTitle();
        foreach($spec->getSpecificationBag() as $subSpec) {
          list($name, $description) = self::addMappingField($subSpec, $prefix, $current_mapping, $form, $availableFields);
          $actionProviderGroupedMappingFields[$prefix][$spec->getName()]['fields'][] = $name;
          if ($description) {
            $actionProviderMappingDescriptions[$prefix][$name] = $description;
          }
        }
      } else {
        list($name, $description) = self::addMappingField($spec, $prefix, $current_mapping, $form, $availableFields);
        $actionProviderMappingFields[$prefix][] = $name;
        if ($description) {
          $actionProviderMappingDescriptions[$prefix][$name] = $description;
        }
      }
    }
    $form->assign('actionProviderMappingFields', $actionProviderMappingFields);
    $form->assign('actionProviderGroupedMappingFields', $actionProviderGroupedMappingFields);
    $form->assign('actionProviderMappingDescriptions', $actionProviderMappingDescriptions);
  }

  protected static function addMappingField($spec, $prefix, $current_mapping, \CRM_Core_Form $form, $availableFields) {
    $name = $prefix.'mapping_'.$spec->getName();
    $description = null;
    if ($spec->getDescription()) {
      $description = $spec->getDescription();
    }
    if ($spec instanceof \Civi\ActionProvider\Parameter\SpecificationCollection) {
      // TODO implement specification collection
      // Not implemented because it was not needed for the current use case.
      // Yes I know that is a bit lazy.
      // But this requires a bit work on the template as well as we need to implement a table
      // with dynamic rows.
    } else {
      $form->add('select', $name, $spec->getTitle(), $availableFields, $spec->isRequired(), [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
        'multiple' => $spec->isMultiple(),
      ]);
    }

    if (isset($current_mapping[$spec->getName()])) {
      $defaults[$name] = $current_mapping[$spec->getName()];
      $form->setDefaults($defaults);
    }

    return [$name, $description];
  }

  public static function processMapping($submittedValues, $prefix, \Civi\ActionProvider\Parameter\SpecificationBag $specificationBag) {
    $return = array();
    foreach($specificationBag as $spec) {
      if (method_exists($spec, 'getSpecificationBag')) {
        $result = self::processMapping($submittedValues, $prefix, $spec->getSpecificationBag() );
        $return = array_merge($return,$result);
      } else {
        $name = $prefix.'mapping_'.$spec->getName();
        if (isset($submittedValues[$name])) {
          $return[$spec->getName()] = $submittedValues[$name];
        }}
    }
    return $return;
  }

}
