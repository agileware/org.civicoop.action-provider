<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateUpdateOrganization extends AbstractAction {

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
    $contact_sub_type = $this->configuration->getParameter('contact_sub_type');
    $params['contact_type'] = "Organization";
    if ($contact_sub_type) {
      $params['contact_sub_type'] = $contact_sub_type;
    }
    $params['organization_name'] = $parameters->getParameter('organization_name');
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
    $contactSubTypes = array();
    $contactSubTypesApi = civicrm_api3('ContactType', 'get', array('parent_id' => 'Organization', 'options' => array('limit' => 0)));
    $contactSubTypes[''] = E::ts(' - Select - ');
    foreach($contactSubTypesApi['values'] as $contactSubType) {
      $contactSubTypes[$contactSubType['name']] = $contactSubType['label'];
    }

    $spec = new SpecificationBag(array(
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), false, null, null, $contactSubTypes, FALSE),
    ));

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
    $contactIdSpec = new Specification('contact_id', 'Integer', E::ts('Contact ID'), false);
    $contactIdSpec->setDescription(E::ts('Leave empty to create a new Organization'));
    $spec = new SpecificationBag(array(
      $contactIdSpec,
      new Specification('organization_name', 'String', E::ts('Organization name'), false),
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

}
