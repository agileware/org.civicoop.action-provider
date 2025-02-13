<?php

/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

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

    // Configuration
    if ($this->configuration->doesParameterExists('status_id') && !empty($this->configuration->getParameter('status_id'))) {
      $apiParams['status_id']['IN']     = array_filter((array) $this->configuration->getParameter('status_id')); // Strips out empty strings
    }

    if ($this->configuration->doesParameterExists('order_by') && !empty($this->configuration->getParameter('order_by'))) {
      $apiParams['sort']                = $this->configuration->getParameter('order_by');
    }

    if ($this->configuration->doesParameterExists('sort_order') && !empty($this->configuration->getParameter('sort_order'))) {
      $apiParams['sort']                = implode( ' ', [$apiParams['sort'], $this->configuration->getParameter('sort_order')]);
    }

    // Parameters
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

    $apiParams['is_test'] = FALSE;
    $apiParams['return'] = 'id';

    try {
      $apiParams['options'] = ['limit' => 1];
      $membership_id = civicrm_api3('Membership', 'getvalue', $apiParams);

      if ($membership_id) {
        $output->setParameter('membership_id', $membership_id);
      }

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
    $sort_field['join_date']  = ts('Join Date');
    $sort_field['start_date'] = ts('Start Date');
    $sort_field['end_date']   = ts('End Date');

    $sort_order['ASC']  = ts('Ascending');
    $sort_order['DESC'] = ts('Descending');

		return new SpecificationBag(array(
      new Specification('fail_not_found', 'Boolean', E::ts('Fail on not found'), false, true),
      new Specification('status_id', 'Integer', E::ts('Status'), FALSE, NULL, 'MembershipStatus', NULL, TRUE),
      new Specification('order_by', 'String', E::ts('Select Membership, Order By'), FALSE, 'end_date', NULL, $sort_field, FALSE),
      new Specification('sort_order', 'String', E::ts('Select Membership, Sort Order'), FALSE, 'DESC', NULL, $sort_order, FALSE),
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
