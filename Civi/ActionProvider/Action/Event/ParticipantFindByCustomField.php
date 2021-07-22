<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Exception\InvalidParameterException;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Parameter\SpecificationGroup;
use Civi\ActionProvider\Utils\CustomField;
use CRM_ActionProvider_ExtensionUtil as E;

class ParticipantFindByCustomField extends AbstractAction {

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
    foreach($this->getParameterSpecification() as $specGroup) {
      if (!$specGroup instanceof SpecificationGroup) {
        continue;
      }
      foreach($specGroup->getSpecificationBag() as $spec) {
        if ($parameters->doesParameterExists($spec->getName())) {
          $apiParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
        }
      }
    }
		if (!count($apiParams)) {
		  throw new InvalidParameterException(E::ts("No parameter given"));
    }
		if ($parameters->getParameter('event_id')) {
		  $apiParams['event_id'] = $parameters->getParameter('event_id');
    }

    $apiParams['return'] = 'id';
    try {
      $participant = civicrm_api3('Participant', 'getsingle', $apiParams);
      $output->setParameter('participant_id', $participant['id']);
      $output->setParameter('contact_id', $participant['contact_id']);
      $output->setParameter('evenet_id', $participant['evenet_id']);
    } catch (\CiviCRM_API3_Exception $ex) {
      if ($fail) {
        throw new ExecutionException('Could not find participant');
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
		$specs = new SpecificationBag([
		  new Specification('event_id', 'Integer', E::ts('Event ID'), false),
    ]);
    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntities(['Participant']);
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
	 * This function could be overriden by child classes.
	 *
	 * @return SpecificationBag
	 */
	public function getOutputSpecification() {
		return new SpecificationBag(array(
			new Specification('participant_id', 'Integer', E::ts('Participant ID'), true),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('event_id', 'Integer', E::ts('Event ID'), true),
		));
	}

}
