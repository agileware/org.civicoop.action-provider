<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateUpdateHousehold extends AbstractAction {

  protected $contactSubTypes;

  public function __construct() {
    $this->contactSubTypes     = [];
    $contactSubTypesApi        = civicrm_api3('ContactType', 'get', [
      'parent_id' => 'Household',
      'options'   => ['limit' => 0],
    ]);
    $this->contactSubTypes[''] = E::ts(' - Select - ');
    foreach ($contactSubTypesApi['values'] as $contactSubType) {
      $this->contactSubTypes[$contactSubType['name']] = $contactSubType['label'];
    }
  }

  /**
   * Run the action
   *
   * @param   ParameterInterface     $parameters
   *   The parameters to this action.
   * @param   ParameterBagInterface  $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Create contact
    if ($parameters->getParameter('contact_id')) {
      $params['id'] = $parameters->getParameter('contact_id');
    }
    if ($parameters->doesParameterExists('external_identifier')) {
      $params['external_identifier'] = $parameters->getParameter('external_identifier');
    }
    $contact_sub_type = FALSE;
    if ($parameters->doesParameterExists('contact_sub_type')) {
      $contact_sub_type = $parameters->getParameter('contact_sub_type');
    }
    elseif ($this->configuration->doesParameterExists('contact_sub_type')) {
      $contact_sub_type = $this->configuration->getParameter('contact_sub_type');
    }
    $params['contact_type'] = "Household";
    if ($contact_sub_type) {
      $params['contact_sub_type'] = $contact_sub_type;
    }
    if ($parameters->doesParameterExists('household_name')) {
      $params['household_name'] = $parameters->getParameter('household_name');
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
    $result     = civicrm_api3('Contact', 'create', $params);
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
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $spec = new SpecificationBag([
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), FALSE, NULL, NULL, $this->contactSubTypes, TRUE),
    ]);

    ContactActionUtils::createAddressConfigurationSpecification($spec);
    ContactActionUtils::createEmailConfigurationSpecification($spec);
    ContactActionUtils::createPhoneConfigurationSpecification($spec);

    return $spec;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $contactIdSpec = new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE);
    $contactIdSpec->setDescription(E::ts('Leave empty to create a new Household'));
    $spec = new SpecificationBag([
      $contactIdSpec,
      new Specification('external_identifier', 'String', E::ts('External Identifier'), false),
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), FALSE, NULL, NULL, $this->contactSubTypes, TRUE),
      new Specification('household_name', 'String', E::ts('Household name'), FALSE),
      new Specification('source', 'String', E::ts('Source'), FALSE),
      new Specification('created_date', 'Date', E::ts('Created Date'), FALSE),
      new Specification('do_not_mail', 'Boolean', E::ts('Do not mail'), FALSE),
      new Specification('do_not_email', 'Boolean', E::ts('Do not e-mail'), FALSE),
      new Specification('do_not_phone', 'Boolean', E::ts('Do not Phone'), FALSE),
      new Specification('do_not_sms', 'Boolean', E::ts('Do not SMS'), FALSE),
    ]);
    ContactActionUtils::createAddressParameterSpecification($spec);
    ContactActionUtils::createEmailParameterSpecification($spec);
    ContactActionUtils::createPhoneParameterSpecification($spec);
    return $spec;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE),
      new Specification('address_id', 'Integer', E::ts('Address record ID'), FALSE),
      new Specification('email_id', 'Integer', E::ts('Email record ID'), FALSE),
      new Specification('phone_id', 'Integer', E::ts('Phone ID'), FALSE),
    ]);
  }

}
