<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetCaseDataById extends AbstractAction {

  protected $skippedFields = ['contacts', 'activities'];

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $case_id = $parameters->getParameter('case_id');
    try {
      $case = civicrm_api3('Case', 'getsingle', ['id' => $case_id]);

      if ($case) {
        foreach($case as $field => $value) {
          if (in_array($field, $this->skippedFields)) {
            continue;
          }
          if (stripos($field, 'custom_') !== 0) {
            $output->setParameter($field, $value);
          } else {
            $custom_id = substr($field, 7);
            if (is_numeric($custom_id)) {
              $fieldName = CustomField::getCustomFieldName($custom_id);
              if (is_array($value)) {
                // The keys of the array contains the values of the selected options.
                $value = array_keys($value);
              }
              $output->setParameter($fieldName, $value);
            }
          }
        }
      }

    } catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing.
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), true),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    $fields = civicrm_api3('Case', 'getfields', array('api_action' => 'get'));
    foreach($fields['values'] as $field) {
      if (in_array($field['name'], $this->skippedFields)) {
        continue;
      }
      if (stripos($field['name'], 'custom_') !== 0) {
        $options = null;
        try {
          $option_api = civicrm_api3('Case', 'getoptions', ['field' => $field['name']]);
          if (isset($option_api['values']) && is_array($option_api['values'])) {
            $options = $option_api['values'];
          }
        } catch (\Exception $e) {
          // Do nothing
        }

        $type = \CRM_Utils_Type::typeToString($field['type']);
        switch ($type) {
          case 'Int':
          case 'ContactReference':
            $type = 'Integer';
            break;
          case 'File':
            $type = null;
            break;
          case 'Memo':
            $type = 'Text';
            break;
          case 'Link':
            $type = 'String';
            break;
        }

        $spec = new Specification($field['name'], $type, $field['title'], false, null, null, $options, false);
        $bag->addSpecification($spec);
      }
    }

    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'is_active' => 1,
      'options' => ['limit' => 0],
    ]);
    foreach ($customGroups['values'] as $customGroup) {
      $customFields = civicrm_api3('CustomField', 'get', [
        'custom_group_id' => $customGroup['id'],
        'is_active' => 1,
        'options' => ['limit' => 0],
      ]);
      foreach ($customFields['values'] as $customField) {
        $spec = CustomField::getSpecFromCustomField($customField, $customGroup['title'] . ': ', FALSE);
        if ($spec) {
          $bag->addSpecification($spec);
        }
      }
    }

    return $bag;
  }


}