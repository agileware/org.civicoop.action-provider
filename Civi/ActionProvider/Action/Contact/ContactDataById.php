<?php

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractGetSingleAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class ContactDataById extends AbstractGetSingleAction {

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
    parent::doAction($parameters, $output);
    // Get custom data
    $custom_data = civicrm_api3('CustomValue', 'get', array('entity_id' => $parameters->getParameter('contact_id'), 'entity_table' => 'civicrm_contact'));
    foreach($custom_data['values'] as $custom) {
      $fieldName = CustomField::getCustomFieldName($custom['id']);
      $output->setParameter($fieldName, $custom['latest']);
    }
  }


  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Contact';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('contact_id');
  }

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		return new SpecificationBag(array(
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
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
    $specs = parent::getOutputSpecification();
    $customGroups = civicrm_api3('CustomGroup', 'get', [
      'extends' => ['IN' => ['Individual', 'Household', 'Organization']],
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
          $specs->addSpecification($spec);
        }
      }
    }
    return $specs;
  }


}
