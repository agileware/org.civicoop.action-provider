<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class FindByCustomField extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Civi\ActionProvider\Exception\ExecutionException
   * @throws \CiviCRM_API3_Exception
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
	protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
	  $fail = $this->configuration->doesParameterExists('fail_not_found') ? $this->configuration->getParameter('fail_not_found') : true;
    $apiParams = array();
    foreach($this->getParameterSpecification() as $spec) {
		  if ($parameters->doesParameterExists($spec->getName())) {
		    $apiParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
      }
    }
		if (!count($apiParams)) {
		  throw new InvalidParameterException(E::ts("No parameter given"));
    }

    if ($this->configuration->getParameter('contact_type')) {
      $contact_type = ContactActionUtils::getContactType($this->configuration->getParameter('contact_type'));
      $apiParams['contact_type'] = $contact_type['contact_type']['name'];
      if ($contact_type['contact_sub_type']) {
        $apiParams['contact_sub_type'] = $contact_type['contact_sub_type']['name'];
      }
    }

    $apiParams['return'] = 'id';
    try {
      $contact_id = civicrm_api3('Contact', 'getvalue', $apiParams);
    } catch (\CiviCRM_API3_Exception $ex) {
      if ($fail) {
        throw new ExecutionException('Could not find contact');
      }
    }

		$output->setParameter('contact_id', $contact_id);
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array(
			new Specification('contact_type', 'Integer', E::ts('Contact type'), false, null, 'ContactType', null, FALSE),
      new Specification('fail_not_found', 'Boolean', E::ts('Fail on not found'), false, true),
		));
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		$specs = new SpecificationBag();
    $customGroups = civicrm_api3('CustomGroup', 'get', array('is_active' => 1, 'is_multiple' => 0, 'options' => array('limit' => 0)));
    foreach($customGroups['values'] as $customGroup) {
      if (!in_array($customGroup['extends'], array('Individual', 'Household', 'Organization', 'Contact'))) {
        continue;
      }

      $customFields = civicrm_api3('CustomField', 'get', array('custom_group_id' => $customGroup['id'], 'is_active' => 1, 'options' => array('limit' => 0)));
      foreach($customFields['values'] as $customField) {
        $spec = CustomField::getSpecFromCustomField($customField, $customGroup['title'].': ', false);
        if ($spec) {
          $specs->addSpecification($spec);
        }
      }
    }
    return $specs;
	}

	/**
	 * Returns the specification of the output parameters of this action.
	 *
	 * This function could be overriden by child classes.
	 *
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		return new SpecificationBag(array(
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
		));
	}

}
