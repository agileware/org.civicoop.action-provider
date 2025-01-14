<?php

namespace Civi\ActionProvider\Action\Membership;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class GetMembershipByCustomField extends AbstractAction {

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
      if ($spec->getName() === 'contact_id') {
        // Where contact ID is parameter value
        $apiParams['contact_id'] = $parameters->getParameter('contact_id');
      }
      else {
        // Set each custom field value
        foreach ($spec->getSpecificationBag() as $subspec) {
          if ($parameters->doesParameterExists($subspec->getName())) {
            $apiParams[$subspec->getApiFieldName()] = $parameters->getParameter($subspec->getName());
          }
        }
      }
    }
		if (!count($apiParams)) {
		  throw new InvalidParameterException(E::ts("No parameter given"));
    }

    $apiParams['return'] = 'id';
    try {
      $apiParams['options'] = ['limit' => 1];
      $membership = civicrm_api3('Membership', 'getvalue', $apiParams);
      $output->setParameter('membership_id', $membership);
    } catch (\CiviCRM_API3_Exception $ex) {
      if ($fail) {
        throw new ExecutionException('Could not find membership');
      }
    }
	}

	/**
	 * Returns the specification of the configuration options for the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag(array(
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
    $specs->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities(['Membership']);
    foreach($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }
    return $specs;
	}

	/**
	 * Returns the specification of the output parameters of this action.
	 *
	 * This function could be overridden by child classes.
	 *
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		return new SpecificationBag(array(
			new Specification('membership_id', 'Integer', E::ts('Membership ID'), FALSE)
		));
	}

}
