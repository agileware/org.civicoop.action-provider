<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class FindByCustomGroupMultiValue extends AbstractAction {

	/**
	 * Run the action
	 *
	 * @param ParameterBagInterface $parameters
	 *   The parameters to this action.
	 * @param ParameterBagInterface $output
	 *   The parameters this action can send back
	 * @return void
	 */
	protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
		$customGroup = civicrm_api4('CustomGroup', 'get', array(
			'select' => array('name'),
			'where' => array(
				array('id', '=', $this->configuration->getParameter('custom_group')),
			),
			'checkPermissions' => $this->configuration->getParameter('check_permission'),
		));
		$customGroupName = $customGroup[0]['name'];
		$apiParams = array(
			'where' => array()
		);
		foreach ($this->getParameterSpecification() as $spec) {
			if ($spec->getName() === 'contact_id') {
				array_push($apiParams['where'], array('entity_id', '=', $parameters->getParameter('contact_id')));
			}
			else if ($spec->getName() === $customGroupName) {
				foreach ($spec->getSpecificationBag() as $subspec) {
					if ($parameters->doesParameterExists($subspec->getName())) {
						$apiSpecName = str_replace('custom_' . $spec->getName() . '_', '', $subspec->getName());
						array_push($apiParams['where'], array($apiSpecName, '=', $parameters->getParameter($subspec->getName())));
					}
				}

			}
		}
		$apiParams['checkPermissions'] = $this->configuration->getParameter('check_permission');

		$apiCustomGroupName = 'Custom_' . $customGroupName;
		if (!count($apiParams)) {
			throw new InvalidParameterException(E::ts("No parameter given"));
		}

		$result = civicrm_api4($apiCustomGroupName, 'get', $apiParams);
		$output->setParameter('custom_group_id', $this->configuration->getParameter('custom_group'));
		if (isset($result[0])) {
			foreach ($result[0] as $key => $value) {
				if ($key === 'id') {
					$output->setParameter('custom_group_entry_id', $value);
				}
				else if ($key === 'entity_id') {
					$output->setParameter($key, $value);
				}
				else {
					$fieldName = 'custom_' . $customGroupName . '_' . $key;
					$output->setParameter($fieldName, $value);
				}
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
			new Specification('custom_group', 'Integer', E::ts('Custom group'), true, null, 'CustomGroup', null, FALSE),
			new Specification('check_permission', 'Boolean', E::ts('Check permissions'), true, null, null, null, FALSE),
		)
		);
	}

	/**
	 * Returns the specification of the parameters of the actual action.
	 *
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		$specs = new SpecificationBag();
		$specs->addSpecification(new Specification('contact_id', 'Integer', E::ts('Contact ID'), true));

		$config = ConfigContainer::getInstance();
		$customGroups = $config->getCustomGroupsForEntities(['Contact', 'Individual', 'Household', 'Organization']);
		foreach ($customGroups as $customGroup) {
			if (!empty($customGroup['is_active']) && $customGroup['is_multiple']) {
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
		$specs = new SpecificationBag();
		$specs->addSpecification(new Specification('custom_group_entry_id', 'Integer', E::ts('Custom Group entry ID'), true, null, null, null, false));
		$specs->addSpecification(new Specification('custom_group_id', 'Integer', E::ts('Custom group ID'), true, null, null, null, false));
		$specs->addSpecification(new Specification('entity_id', 'Integer', E::ts('Entity ID'), true, null, null, null, false));

		$config = ConfigContainer::getInstance();
		$customGroups = $config->getCustomGroupsForEntities(['Contact', 'Individual', 'Household', 'Organization']);
		foreach ($customGroups as $customGroup) {
			if (!empty($customGroup['is_active']) && $customGroup['is_multiple']) {
				$specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
			}
		}

		return $specs;
	}


}
