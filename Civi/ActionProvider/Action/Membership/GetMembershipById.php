<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Membership;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Utils\CustomField;

use Civi\ActionProvider\Utils\Fields;
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
    Fields::getFieldsForEntity($bag,'Membership', 'get', array());
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
