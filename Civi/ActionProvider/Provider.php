<?php

namespace Civi\ActionProvider;

use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\SpecificationBag;
use \CRM_ActionProvider_ExtensionUtil as E;

/**
 * Singleton and conatiner class with all the actions.
 *
 * This class could be overriden by child classes in an extension to provide a context aware container
 * for the actions.
 */
class Provider {

	/**
	 * @var array
	 *   All the actions which are available for use in this context.
	 */
	protected $availableActions = array();

	/**
	 * @var array
	 *   All the actions including the inactive ones.
	 */
	protected $allActions = array();

  /**
   * @var array
   */
	protected $actionTitles = array();

  /**
   * @var array
   */
  protected $acttionTags = array();

  /**
   * @var array
   *   All the condition which are available to be used in this context.
   */
	protected $availableConditions = array();

  /**
   * @var array
   *   Contains all possible conditions.
   */
	protected $allConditions = array();

  /**
   * @var AbstractAction[]
   *   Contains all instanciated actions.
   */
	protected $batchActions = array();

	public function __construct() {
    $this->addActionWithoutFiltering('OptionValueToLabel', '\Civi\ActionProvider\Action\Generic\OptionValueToLabel', E::ts('Show option value(s) as their Label(s)'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
	  $this->addActionWithoutFiltering('SetValue', '\Civi\ActionProvider\Action\Generic\SetValue', E::ts('Set Value'), array(
	    AbstractAction::DATA_MANIPULATION_TAG));
    $this->addActionWithoutFiltering('SetParameterValue', '\Civi\ActionProvider\Action\Generic\SetParameterValue', E::ts('Set Value from parameter'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $this->addActionWithoutFiltering('SetDateValue', '\Civi\ActionProvider\Action\Generic\SetDateValue', E::ts('Set date value'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $this->addActionWithoutFiltering('ConcatDateTimeValue', '\Civi\ActionProvider\Action\Generic\ConcatDateTimeValue', E::ts('Concat (merge) a date and a time field to one field'), array(
      AbstractAction::DATA_MANIPULATION_TAG));
    $this->addActionWithoutFiltering('AddToGroup', '\Civi\ActionProvider\Action\Group\AddToGroup', E::ts('Add to Group'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
      'CiviRules.GroupContactAdd', // This how this action is called in CiviRules
    ));
    $this->addActionWithoutFiltering('AddToGroupParameter', '\Civi\ActionProvider\Action\Group\AddToGroupParameter', E::ts('Add to Group (with Group ID as parameter)'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('RemoveFromGroupParameter', '\Civi\ActionProvider\Action\Group\RemoveFromGroupParameter', E::ts('Remove from  group (with Group ID as parameter)'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateGroup', '\Civi\ActionProvider\Action\Group\Create', E::ts('Create/Update a group'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      'group',
    ));
    $this->addActionWithoutFiltering('GetGroup', '\Civi\ActionProvider\Action\Group\GetGroup', E::ts('Get group data'), array(
      AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $this->addActionWithoutFiltering('DeleteGroup', '\Civi\ActionProvider\Action\Group\DeleteGroup', E::ts('Delete Group'), array(
      AbstractAction::DATA_MANIPULATION_TAG
    ));
    $this->addActionWithoutFiltering('ContactDataById', '\Civi\ActionProvider\Action\Contact\ContactDataById', E::ts('Get contact data by ID'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdateAddress', '\Civi\ActionProvider\Action\Contact\CreateUpdateAddress', E::ts('Create or update address of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdateEmailAddress', '\Civi\ActionProvider\Action\Contact\CreateUpdateEmailAddress', E::ts('Create or update an e-mail address of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('UsePrimaryAddressOfContact', '\Civi\ActionProvider\Action\Contact\UsePrimaryAddressOfContact', E::ts('Use primary address of another contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetAddress', '\Civi\ActionProvider\Action\Contact\GetAddress', E::ts('Get address of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetEmailAddress', '\Civi\ActionProvider\Action\Contact\GetEmailAddress', E::ts('Get e-mail address of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetStateProvinceId', '\Civi\ActionProvider\Action\Contact\GetStateProvinceId', E::ts('Get state/province ID by name'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetAddressById', '\Civi\ActionProvider\Action\Contact\GetAddressById', E::ts('Get address by ID'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetContactIdFromMasterAddress', '\Civi\ActionProvider\Action\Contact\GetContactIdFromMasterAddress', E::ts('Get contact ID of a master address'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('FindIndividualByNameAndEmail', '\Civi\ActionProvider\Action\Contact\FindIndividualByNameAndEmail', E::ts('Find Individual by name and email'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('FindByExternalId', '\Civi\ActionProvider\Action\Contact\FindByExternalId', E::ts('Find contact by external id'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('FindContactByCustomField', '\Civi\ActionProvider\Action\Contact\FindByCustomField', E::ts('Find contact by custom field'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdateIndividual', '\Civi\ActionProvider\Action\Contact\CreateUpdateIndividual', E::ts('Create or update Individual'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdateHousehold', '\Civi\ActionProvider\Action\Contact\CreateUpdateHousehold', E::ts('Create or update Household'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdateOrganization', '\Civi\ActionProvider\Action\Contact\CreateUpdateOrganization', E::ts('Create or update Organization'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('UpdateCustomData', '\Civi\ActionProvider\Action\Contact\UpdateCustomData',E::ts('Update custom data for a contact') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('MarkContactAsDeceased', '\Civi\ActionProvider\Action\Contact\MarkContactAsDeceased',E::ts('Mark contact as deceased') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('SetContactSubType', '\Civi\ActionProvider\Action\Contact\SetContactSubType',E::ts('Set contact subtype') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('UploadCustomFileField', '\Civi\ActionProvider\Action\Contact\UploadCustomFileField',E::ts('Upload file to a custom field for a contact') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('FindOrCreateContactByEmail', '\Civi\ActionProvider\Action\Contact\FindOrCreateContactByEmail', E::ts('Find or create contact by e-mail') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('FindOrCreateCampaign', '\Civi\ActionProvider\Action\Campaign\FindOrCreateCampaign',E::ts('Find or create a campaign') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateActivity', '\Civi\ActionProvider\Action\Activity\CreateActivity',E::ts('Create or update activity') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('UpdateActivityStatus', '\Civi\ActionProvider\Action\Activity\UpdateActivityStatus',E::ts('Update activity status') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('DeleteActivity', '\Civi\ActionProvider\Action\Activity\DeleteActivity', E::ts('Delete activity'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('GetActivity', '\Civi\ActionProvider\Action\Activity\GetActivity', E::ts('Get activity data'), array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetActivityContact', '\Civi\ActionProvider\Action\Activity\GetActivityContact', E::ts('Get contact IDs from an activity'), array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('Send', '\Civi\ActionProvider\Action\BulkMail\Send',E::ts('Send Bulk Mail') , array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
      'bulk_mail'
    ));
    $this->addActionWithoutFiltering('AddAttachmentToBulkMail', '\Civi\ActionProvider\Action\BulkMail\AddAttachment', E::ts('Add attachment to Bulk Mail'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('UpdateParticipantStatus', '\Civi\ActionProvider\Action\Event\UpdateParticipantStatus',E::ts('Update participant status') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('UpdateParticipantStatusWithDynamicStatus', '\Civi\ActionProvider\Action\Event\UpdateParticipantStatusWithDynamicStatus',E::ts('Update participant status (with dynamic status)') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateOrUpdateParticipant', '\Civi\ActionProvider\Action\Event\CreateOrUpdateParticipant',E::ts('Register contact for an event') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateOrUpdateParticipantWithDynamicStatus', '\Civi\ActionProvider\Action\Event\CreateOrUpdateParticipantWithDynamicStatus', E::ts('Register contact for an event (with dynamic status)') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateOrUpdateEvent', '\Civi\ActionProvider\Action\Event\CreateOrUpdateEvent', E::ts('Create or update an event') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateRecurringEvent', '\Civi\ActionProvider\Action\Event\CreateRecurringEvent', E::ts('Repeat an event') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetRecurringEvent', '\Civi\ActionProvider\Action\Event\GetRecurringEvent', E::ts('Get event repetition') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetEvent', '\Civi\ActionProvider\Action\Event\GetEvent',E::ts('Get event data') , array(
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('EventUploadCustomFileField', '\Civi\ActionProvider\Action\Event\UploadCustomFileField',E::ts('Upload file to a custom field for an event') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('DeleteEvent', '\Civi\ActionProvider\Action\Event\DeleteEvent',E::ts('Delete event') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetParticipant', '\Civi\ActionProvider\Action\Event\GetParticipant',E::ts('Get participant data') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('DeleteParticipant', '\Civi\ActionProvider\Action\Event\DeleteParticipant', E::ts('Delete participant'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('SendEmailToParticipants', '\Civi\ActionProvider\Action\Event\SendEmailToParticipants', E::ts('Send e-mail to participants'), array(
      AbstractAction::SEND_MESSAGES_TO_CONTACTS,
    ));
    $this->addActionWithoutFiltering('UpdateEventStatus', '\Civi\ActionProvider\Action\Event\UpdateEventStatus', E::ts('Update Event Status') , array(
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetRelationship', '\Civi\ActionProvider\Action\Relationship\GetRelationship',E::ts('Get relationship') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetRelationshipByContactId', '\Civi\ActionProvider\Action\Relationship\GetRelationshipByContactId',E::ts('Get relationship by Contact ID') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('CreateRelationship', '\Civi\ActionProvider\Action\Relationship\CreateRelationship',E::ts('Create relationship') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateOrUpdateRelationship', '\Civi\ActionProvider\Action\Relationship\CreateOrUpdateRelationship',E::ts('Creat/Update relationship') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('EndRelationship', '\Civi\ActionProvider\Action\Relationship\EndRelationship',E::ts('End relationship') , array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdateWebsite', '\Civi\ActionProvider\Action\Website\CreateUpdateWebsite',E::ts('Create or update website of a contact') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetWebsite', '\Civi\ActionProvider\Action\Website\GetWebsite', E::ts('Get website url of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('CreateUpdatePhone', '\Civi\ActionProvider\Action\Phone\CreateUpdatePhone',E::ts('Create or update phonenumber of a contact') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetPhone', '\Civi\ActionProvider\Action\Phone\GetPhone', E::ts('Get phonenumber of a contact'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('CreateOrUpdateMembership', '\Civi\ActionProvider\Action\Membership\CreateOrUpdateMembership',E::ts('Create or update an membership') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('CreateOrUpdateMembershipWithTypeParameter', '\Civi\ActionProvider\Action\Membership\CreateOrUpdateMembershipWithTypeParameter',E::ts('Create or update an membership (with type as parameter)') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('UpdateMembership', '\Civi\ActionProvider\Action\Membership\UpdateMembership',E::ts('Update an membership') , array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    ));
    $this->addActionWithoutFiltering('GetMembershipById', '\Civi\ActionProvider\Action\Membership\GetMembershipById',E::ts('Get membership by ID') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('GetMembershipType', '\Civi\ActionProvider\Action\Membership\GetMembershipType',E::ts('Get membership type data') , array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG,
    ));
    $this->addActionWithoutFiltering('MessageTemplateByName', '\Civi\ActionProvider\Action\Communication\MessageTemplateByName', E::ts('Find message template by name'), array(
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG,
      AbstractAction::DATA_RETRIEVAL_TAG
    ));
    $this->addActionWithoutFiltering('SendEmail', '\Civi\ActionProvider\Action\Communication\SendEmail', E::ts('Send E-mail'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS
    ));
    $this->addActionWithoutFiltering('CreatePdf', '\Civi\ActionProvider\Action\Communication\CreatePdf', E::ts('Create PDF'), array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::SEND_MESSAGES_TO_CONTACTS
    ));
    $this->addActionWithoutFiltering('CreateContribution', '\Civi\ActionProvider\Action\Contribution\CreateContribution', E::ts('Create contribution'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('LinkContributionToMembership', '\Civi\ActionProvider\Action\Contribution\LinkContributionToMembership', E::ts('Link contribution to membership'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::WITHOUT_CONTACT_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('CreateSoftContribution', '\Civi\ActionProvider\Action\Contribution\CreateSoftContribution', E::ts('Create soft contribution'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('AddTagToContact', '\Civi\ActionProvider\Action\Tag\AddTagToContact', E::ts('Add tag to contact'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));
    $this->addActionWithoutFiltering('CreateCase', '\Civi\ActionProvider\Action\CiviCase\CreateCase', E::ts('Create case'), array(
      AbstractAction::DATA_MANIPULATION_TAG,
      AbstractAction::SINGLE_CONTACT_ACTION_TAG
    ));

		$conditions = array(
		  new \Civi\ActionProvider\Condition\ParameterIsEmpty(),
      new \Civi\ActionProvider\Condition\ParameterIsNotEmpty(),
      new \Civi\ActionProvider\Condition\ParameterHasValue(),
      new \Civi\ActionProvider\Condition\ParametersMatch(),
      new \Civi\ActionProvider\Condition\ParametersDontMatch(),
      new \Civi\ActionProvider\Condition\CheckParameters(),
      new \Civi\ActionProvider\Condition\ContactHasSubtype(),
      new \Civi\ActionProvider\Condition\ContactHasTag(),
    );

    foreach($conditions as $condition) {
      $condition->setProvider($this);
      $this->allConditions[$condition->getName()] = $condition;
    }

		$this->availableActions = array_filter($this->allActions, array($this, 'filterActions'));
    $this->availableConditions = array_filter($this->allConditions, array($this, 'filterConditions'));
	}

	/**
	 * Returns all available actions
	 */
	public function getActions() {
		return $this->availableActions;
	}

	public function getActionTitles() {
	  $titles = array();
	  foreach($this->availableActions as $actionName => $actionClass) {
	    if (isset($this->actionTitles[$actionName])) {
        $titles[$actionName] = $this->actionTitles[$actionName];
      }
    }
    return $titles;
  }

	/**
	 * Adds an action to the list of available actions.
	 *
	 * This function might be used by extensions to add their own actions to the system.
	 *
	 * @param String $name
   * @param String $className
   * @param String $title
   * @param String[] $tags
	 * @return Provider
	 */
	public function addAction($name, $className, $title, $tags=array()) {
		$this->addActionWithoutFiltering($name, $className, $title, $tags);
		$this->availableActions = array_filter($this->allActions, array($this, 'filterActions'));
		return $this;
	}

  /**
   * Adds an action to the list of available actions.
   *
   * This function might be used by extensions to add their own actions to the system.
   *
   * @param String $name
   * @param String $className
   * @param String $title
   * @param String[] $tags
   * @return Provider
   */
  private function addActionWithoutFiltering($name, $className, $title, $tags=array()) {
    $this->allActions[$name] = $className;
    $this->actionTitles[$name] = $title;
    $this->acttionTags[$name] = $tags;
    return $this;
  }

	/**
	 * Returns an action by its name.
	 *
	 * @return \Civi\ActionProvider\Action\AbstractAction|null when action is not found.
	 */
	public function getActionByName($name) {
		if (isset($this->availableActions[$name])) {
			$action = new $this->availableActions[$name];
			$action->setProvider($this);
			$action->setDefaults();
			return $action;
		}
		return null;
	}

  /**
   * Returns an action and store the instance to use in batch mode
   *
   * @return \Civi\ActionProvider\Action\AbstractAction|null when action is not found.
   */
  public function getBatchActionByName($name, $configuration, $batchName) {
    if (!isset($this->batchActions[$batchName])) {
      $this->batchActions[$batchName] = array();
    }
    if (!isset($this->batchActions[$batchName][$name])) {
      $this->batchActions[$batchName][$name] = $this->getActionByName($name);
      if (!$this->batchActions[$batchName][$name]) {
        return null;
      }
      $this->batchActions[$batchName][$name]->getConfiguration()->fromArray($configuration);
      $this->batchActions[$batchName][$name]->initializeBatch($batchName);
    }
    return $this->batchActions[$batchName][$name];
  }

  /**
   * Finish a batch
   *
   * @param $batchName
   * @param bool $isLastBatch
   */
  public function finishBatch($batchName, $isLastBatch=false) {
    if (isset($this->batchActions[$batchName])) {
      foreach($this->batchActions[$batchName] as $actionName => $action) {
        $action->finishBatch($batchName, $isLastBatch);
        unset($this->batchActions[$batchName][$actionName]);
      }
    }
  }

  /**
   * Returns all available conditins
   */
  public function getConditions() {
    return $this->availableConditions;
  }

  /**
   * Adds a condition to the list of available conditions.
   *
   * This function might be used by extensions to add their own conditions to the system.
   *
   * @param \Civi\ActionProvider\Condition\AbstractCondition $condition
   * @return Provider
   * @throws \Exception
   */
  public function addCondition(\Civi\ActionProvider\Condition\AbstractCondition $condition) {
    $condition->setProvider($this);
    $this->allConditions[$condition->getName()] = $condition;
    $this->availableConditions = array_filter($this->allConditions, array($this, 'filterConditions'));
    return $this;
  }

  /**
   * Returns a condition by its name.
   *
   * @return \Civi\ActionProvider\Condition\AbstractCondition|null when condition is not found.
   */
  public function getConditionByName($name) {
    if (isset($this->availableConditions[$name])) {
      $condition = clone $this->availableConditions[$name];
      $condition->setProvider($this);
      $condition->setDefaults();
      return $condition;
    }
    return null;
  }

	/**
	 * Returns a new ParameterBag
	 *
	 * This function exists so we can encapsulate the creation of a ParameterBag to the provider.
	 *
	 * @return ParameterBagInterface
	 */
	public function createParameterBag() {
		return new ParameterBag();
	}

	/**
	 * Returns a new parameter bag based on the given mapping.
	 *
	 * @param ParameterBagInterface $parameterBag
	 * @param array $mapping
	 * @return ParameterBagInterface
	 */
	public function createdMappedParameterBag(ParameterBagInterface $parameterBag, $mapping) {
		$mappedParameterBag = $this->createParameterBag();
		foreach($mapping as $mappedField => $field) {
		  if (is_array($field)) {
        $subParameterBags = array();
		    foreach($field as $subField) {
		      if (isset($subField['parameter_mapping'])) {
            $subParameterBags[] = $this->createdMappedParameterBag($parameterBag, $subField['parameter_mapping']);
          }
        }
		    $mappedParameterBag->setParameter($mappedField, $subParameterBags);
      } elseif ($parameterBag->doesParameterExists($field)) {
				$mappedParameterBag->setParameter($mappedField, $parameterBag->getParameter($field));
			}
		}
		return $mappedParameterBag;
	}

	/**
	 * Filter the actions array and keep certain actions.
	 *
	 * This function might be override in a child class to filter out certain actions which do
	 * not make sense in that context. E.g. for example CiviRules has already a AddContactToGroup action
	 * so it does not make sense to use the one provided by us.
	 *
	 * @param string
	 *   The action to filter.
	 * @return bool
	 *   Returns true when the element is valid, false when the element should be disregarded.
	 */
	protected function filterActions($actionName) {
		return true;
	}

  /**
   * Filter the conditions array and keep certain condition.
   *
   * This function might be override in a child class to filter out certain conditions which do
   * not make sense in that context.
   *
   * @param \Civi\ActionProvider\Condition\AbstractCondition $condition
   *   The condition to filter.
   * @return bool
   *   Returns true when the element is valid, false when the element should be disregarded.
   */
  protected function filterConditions(\Civi\ActionProvider\Condition\AbstractCondition $condition) {
    return true;
  }

}
