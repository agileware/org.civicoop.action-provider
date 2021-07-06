<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidParameterException;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateUpdateIndividual extends AbstractAction {

  protected $contactSubTypes;

  public function __construct() {
    $this->contactSubTypes = array();
    $contactSubTypesApi = civicrm_api3('ContactType', 'get', array('parent_id' => 'Individual', 'options' => array('limit' => 0)));
    $this->contactSubTypes[''] = E::ts(' - Select - ');
    foreach($contactSubTypesApi['values'] as $contactSubType) {
      $this->contactSubTypes[$contactSubType['name']] = $contactSubType['label'];
    }
  }


  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Create contact
    if ($parameters->getParameter('contact_id')) {
      $params['id'] = $parameters->getParameter('contact_id');
    }
    $contact_sub_type = false;
    if ($parameters->doesParameterExists('contact_sub_type')) {
      $contact_sub_type = $parameters->getParameter('contact_sub_type');
    } elseif ($this->configuration->doesParameterExists('contact_sub_type')) {
      $contact_sub_type = $this->configuration->getParameter('contact_sub_type');
    }
    $params['contact_type'] = "Individual";
    if ($contact_sub_type) {
      $params['contact_sub_type'] = $contact_sub_type;
    }
    if ($parameters->doesParameterExists('first_name')) {
      $params['first_name'] = $parameters->getParameter('first_name');
    }
    if ($parameters->doesParameterExists('last_name')) {
      $params['last_name'] = $parameters->getParameter('last_name');
    }
    if ($parameters->getParameter('middle_name')) {
      $params['middle_name'] = $parameters->getParameter('middle_name');
    }
    if ($parameters->doesParameterExists('formal_title')) {
      $params['formal_title'] = $parameters->getParameter('formal_title');
    }
    if ($parameters->getParameter('job_title')) {
      $params['job_title'] = $parameters->getParameter('job_title');
    }
    if ($parameters->getParameter('birth_date')) {
      $params['birth_date'] = $parameters->getParameter('birth_date');
    }
    if ($parameters->doesParameterExists('is_deceased')) {
      $params['is_deceased'] = $parameters->getParameter('is_deceased')  ? '1' : '0';;
    }
    if ($parameters->getParameter('deceased_date') && $parameters->getParameter('is_deceased') && $parameters->doesParameterExists('is_deceased') && $params['is_deceased'] ) {
      $params['deceased_date'] = $parameters->getParameter('deceased_date');
    }
    if ($parameters->getParameter('gender_id')) {
      $params['gender_id'] = $parameters->getParameter('gender_id');
    }
    if ($parameters->getParameter('individual_prefix')) {
      $params['individual_prefix'] = $parameters->getParameter('individual_prefix');
    }
    if ($parameters->getParameter('individual_suffix')) {
      $params['individual_suffix'] = $parameters->getParameter('individual_suffix');
    }
    if ($parameters->doesParameterExists('source')) {
      $params['source'] = $parameters->getParameter('source');
    }
    if ($parameters->doesParameterExists('created_date')) {
      $params['created_date'] = $parameters->getParameter('created_date');
    }
    if ($parameters->doesParameterExists('do_not_mail')) {
      $params['do_not_mail'] = $parameters->getParameter('do_not_mail') ? '1' : '0';
    }
    if ($parameters->doesParameterExists('do_not_email')) {
      $params['do_not_email'] = $parameters->getParameter('do_not_email') ? '1' : '0';
    }
    if ($parameters->doesParameterExists('do_not_phone')) {
      $params['do_not_phone'] = $parameters->getParameter('do_not_phone') ? '1' : '0';
    }
    if ($parameters->doesParameterExists('do_not_sms')) {
      $params['do_not_sms'] = $parameters->getParameter('do_not_sms') ? '1' : '0';
    }

    if ($parameters->doesParameterExists('email') && empty ($params['id']) && empty ($params['first_name']) && empty ($params['last_name'])) {
      // When we create a contact and no name is set set the display name to the e-mail address.
      $params['display_name'] = $parameters->getParameter('email');
    }

    $result = civicrm_api3('Contact', 'create', $params);
    $contact_id = $result['id'];
    $output->setParameter('contact_id', $contact_id);

    // Set created date.
    if ($parameters->doesParameterExists('created_date')) {
      ContactActionUtils::setCreatedDate($contact_id, $parameters->getParameter('created_date'));
    }

    // Create address
    $address_id = ContactActionUtils::createAddressForContact($contact_id, $parameters, $this->configuration);
    if ($address_id) {
      $output->setParameter('address_id', $address_id);
    }

    // Create email
    $email_id = ContactActionUtils::createEmail($contact_id, $parameters, $this->configuration);
    if ($email_id) {
      $output->setParameter('email_id', $email_id);
    }

    // Create phone
    $phone_id = ContactActionUtils::createPhone($contact_id, $parameters, $this->configuration);
    if ($phone_id) {
      $output->setParameter('phone_id', $phone_id);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $specs = new SpecificationBag(array(
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), false, null, null, $this->contactSubTypes, TRUE),
    ));

    ContactActionUtils::createAddressConfigurationSpecification($specs);
    ContactActionUtils::createEmailConfigurationSpecification($specs);
    ContactActionUtils::createPhoneConfigurationSpecification($specs);

    return $specs;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $contactIdSpec = new Specification('contact_id', 'Integer', E::ts('Contact ID'), false);
    $contactIdSpec->setDescription(E::ts('Leave empty to create a new Individual'));
    $spec = new SpecificationBag(array(
      $contactIdSpec,
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), false, null, null, $this->contactSubTypes, TRUE),
      new OptionGroupSpecification('individual_prefix', 'individual_prefix', E::ts('Individual prefix'), false),
      new Specification('first_name', 'String', E::ts('First name'), false),
      new Specification('last_name', 'String', E::ts('Last name'), false),
      new Specification('middle_name', 'String', E::ts('Middle name'), false),
      new OptionGroupSpecification('individual_suffix', 'individual_suffix', E::ts('Individual suffix'), false),
      new Specification('formal_title', 'String', E::ts('Formal Title'), false),
      new Specification('job_title', 'String', E::ts('Job Title'), false),
      new Specification('birth_date', 'Date', E::ts('Birth date'), false),
      new Specification('is_deceased', 'Boolean', E::ts('Is deceased'), false),
      new Specification('deceased_date', 'Date', E::ts('Deceased date'), false),
      new OptionGroupSpecification('gender_id', 'gender', E::ts('Gender'), false),
      new Specification('source', 'String', E::ts('Source'), false),
      new Specification('created_date', 'Date', E::ts('Created Date'), false),
      new Specification('do_not_mail', 'Boolean', E::ts('Do not mail'), false),
      new Specification('do_not_email', 'Boolean', E::ts('Do not e-mail'), false),
      new Specification('do_not_phone', 'Boolean', E::ts('Do not Phone'), false),
      new Specification('do_not_sms', 'Boolean', E::ts('Do not SMS'), false),
    ));
    ContactActionUtils::createAddressParameterSpecification($spec);
    ContactActionUtils::createEmailParameterSpecification($spec);
    ContactActionUtils::createPhoneParameterSpecification($spec);
    return $spec;
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
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), false),
      new Specification('address_id', 'Integer', E::ts('Address record ID'), false),
      new Specification('email_id', 'Integer', E::ts('Email record ID'), false),
      new Specification('phone_id', 'Integer', E::ts('Phone ID'), false),
    ));
  }

  /**
   * @return bool
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  protected function validateParameters(ParameterBagInterface $parameters) {
    $return = parent::validateParameters($parameters);
    if (!$parameters->doesParameterExists('contact_id')) {
      if (!$parameters->doesParameterExists('first_name') && !$parameters->doesParameterExists('last_name') && !$parameters->doesParameterExists('email')) {
        throw new InvalidParameterException("Please provide a valid first name, last name or e-mail.");
      }
    }
    return $return;
  }


}
