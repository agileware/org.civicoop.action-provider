<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Membership;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\Membership\Parameter\MembershipTypeSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class GetMembershipById extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array());
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('membership_id', 'Integer', E::ts('Membership ID'), true),
    ));
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
    $fields = civicrm_api3('Membership', 'getfields', array('api_action' => 'get'));
    foreach($fields['values'] as $field) {
      if (stripos($field['name'], 'custom_') !== 0) {
        $options = null;
        try {
          $option_api = civicrm_api3('Membership', 'getoptions', ['field' => $field['name']]);
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
      'extends' => 'Membership',
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

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    try {
      $membership_id = $parameters->getParameter('membership_id');
      $membership = civicrm_api3('Membership', 'getsingle', array('id' => $membership_id));
      if ($membership) {
        foreach($membership as $field => $value) {
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
    } catch (\Exception $e) {
      // Do nothing
    }
  }



}