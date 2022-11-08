<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Utils\UserInterface;

use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\SpecificationCollection;
use Civi\ActionProvider\Parameter\SpecificationGroup;
use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_Form;

class AddMappingToQuickForm {

  public static function addMapping($prefix, SpecificationBag $parameterSpecs, $current_mapping, CRM_Core_Form $form, $availableFields) {
    $actionProviderMappingFields = $form->get_template_vars('actionProviderMappingFields');
    if (!is_array($actionProviderMappingFields)) {
      $actionProviderMappingFields = array();
    }
    $actionProviderMappingDescriptions = $form->get_template_vars('actionProviderMappingDescriptions');
    if (!is_array($actionProviderMappingDescriptions)) {
      $actionProviderMappingDescriptions = array();
    }
    $actionProviderGroupedMappingFields = $form->get_template_vars('actionProviderGroupedMappingFields');
    if (!is_array($actionProviderGroupedMappingFields)) {
      $actionProviderGroupedMappingFields = array();
    }
    $actionProviderCollectionMappingFields = $form->get_template_vars('actionProviderCollectionMappingFields');
    if (!is_array($actionProviderCollectionMappingFields)) {
      $actionProviderCollectionMappingFields = array();
    }
    foreach($parameterSpecs as $spec) {
      if ($spec instanceof SpecificationGroup) {
        $actionProviderGroupedMappingFields[$prefix][$spec->getName()]['title'] = $spec->getTitle();
        foreach($spec->getSpecificationBag() as $subSpec) {
          [$name, $description] = self::addMappingField($subSpec, $prefix, $current_mapping, $form, $availableFields);
          $actionProviderGroupedMappingFields[$prefix][$spec->getName()]['fields'][] = $name;
          if ($description) {
            $actionProviderMappingDescriptions[$prefix][$name] = $description;
          }
        }
      } elseif ($spec instanceof SpecificationCollection) {
        $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['title'] = $spec->getTitle();
        $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['name'] = $spec->getName();
        $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['min'] = $spec->getMin();
        $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['max'] = $spec->getMax();

        $currentCount = 0;
        if (isset($current_mapping[$spec->getName()]) && is_array($current_mapping[$spec->getName()])) {
          $currentCount = count($current_mapping[$spec->getName()]);
        }
        $countName = $spec->getName().'Count';
        $form->add('hidden', $countName);
        $count = $form->getSubmitValue($countName) ? $form->getSubmitValue($countName) : $currentCount;
        $defaults[$countName] = $count;
        $form->setDefaults($defaults);
        $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['count'] = $count;
        $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['elements'] = [];
        for($i=1; $i<=$count; $i++) {
          $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['elements'][] = $i;
        }

        foreach($spec->getSpecificationBag() as $subSpec) {
          [$name, $description] = self::addCollectionMappingField($subSpec, $prefix, $current_mapping, $form, $availableFields, $count, $spec->getName());
          $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['fields'][] = $name;
          $actionProviderCollectionMappingFields[$prefix][$spec->getName()]['is_required'][$name] = $subSpec->isRequired();
          if ($description) {
            $actionProviderMappingDescriptions[$prefix][$name] = $description;
          }
        }
      } else {
        [$name, $description] = self::addMappingField($spec, $prefix, $current_mapping, $form, $availableFields);
        $actionProviderMappingFields[$prefix][] = $name;
        if ($description) {
          $actionProviderMappingDescriptions[$prefix][$name] = $description;
        }
      }
    }
    $form->assign('actionProviderMappingFields', $actionProviderMappingFields);
    $form->assign('actionProviderGroupedMappingFields', $actionProviderGroupedMappingFields);
    $form->assign('actionProviderCollectionMappingFields', $actionProviderCollectionMappingFields);
    $form->assign('actionProviderMappingDescriptions', $actionProviderMappingDescriptions);
  }

  protected static function addMappingField($spec, $prefix, $current_mapping, CRM_Core_Form $form, $availableFields) {
    $name = $prefix.'mapping_'.$spec->getName();
    $description = null;
    if ($spec->getDescription()) {
      $description = $spec->getDescription();
    }
    $form->add('select', $name, $spec->getTitle(), $availableFields, $spec->isRequired(), [
      'style' => 'min-width:250px',
      'class' => 'crm-select2 huge',
      'placeholder' => E::ts('- select -'),
      'multiple' => $spec->isMultiple(),
    ]);

    if (isset($current_mapping[$spec->getName()])) {
      $defaults[$name] = $current_mapping[$spec->getName()];
      $form->setDefaults($defaults);
    }

    return [$name, $description];
  }

  protected static function addCollectionMappingField($spec, $prefix, $current_mapping, CRM_Core_Form $form, $availableFields, $count, $groupName) {
    $singleName = $prefix.'mapping_'.$spec->getName();
    $description = null;
    if ($spec->getDescription()) {
      $description = $spec->getDescription();
    }
    for($i=0; $i <= $count; $i++) {
      $isRequired = $spec->isRequired();
      if ($i == 0) {
        $isRequired = false;
      }
      $form->add('select', $singleName.'['.$i.']', $spec->getTitle(), $availableFields, $isRequired, [
        'style' => 'min-width:250px',
        'class' => 'crm-select2 huge',
        'placeholder' => E::ts('- select -'),
        'multiple' => $spec->isMultiple(),
        'data-single-name' => $singleName,
      ]);
      if ($i >= 1 && isset($current_mapping[$groupName]) && isset($current_mapping[$groupName][$i-1]) && isset($current_mapping[$groupName][$i-1]['parameter_mapping']) && isset($current_mapping[$groupName][$i-1]['parameter_mapping'][$spec->getName()])) {
        $defaults[$singleName.'['.$i.']'] = $current_mapping[$groupName][$i-1]['parameter_mapping'][$spec->getName()];
        $form->setDefaults($defaults);
      }
    }
    return [$singleName, $description];
  }

  public static function processMapping($submittedValues, $prefix, SpecificationBag $specificationBag) {
    $return = array();
    foreach($specificationBag as $spec) {
      if ($spec instanceof SpecificationGroup) {
        $result = self::processMapping($submittedValues, $prefix, $spec->getSpecificationBag());
        $return = array_merge($return, $result);
      } elseif ($spec instanceof SpecificationCollection) {
        $result[$spec->getName()] = [];
        $count = $submittedValues[$spec->getName().'Count'];
        for($i=1; $i <= $count; $i++) {
          foreach($spec->getSpecificationBag() as $subSpec) {
            $name = $prefix.'mapping_'.$subSpec->getName();
            if (isset($submittedValues[$name])) {
              $return[$spec->getName()][$i-1]['parameter_mapping'][$subSpec->getName()] = $submittedValues[$name][$i];
            }
          }
        }
      } else {
        $name = $prefix.'mapping_'.$spec->getName();
        if (isset($submittedValues[$name])) {
          $return[$spec->getName()] = $submittedValues[$name];
        }}
    }
    return $return;
  }

}
