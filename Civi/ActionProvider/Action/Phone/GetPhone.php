<?php

namespace Civi\ActionProvider\Action\Phone;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;

use CRM_ActionProvider_ExtensionUtil as E;

class GetPhone extends AbstractAction {
  
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
    $contact_id = $parameters->getParameter('contact_id');
    $existingPhoneParams['contact_id'] = $contact_id;
    $existingPhoneParams['phone_type_id'] = $this->configuration->getParameter('phone_type');
    $existingPhoneParams['location_type_id'] = $this->configuration->getParameter('location_type');
    $existingPhoneParams['return'] = 'phone';
    try {
      $existingPhone = civicrm_api3('Phone', 'getvalue', $existingPhoneParams);
      $output->setParameter('phone', $existingPhone);
    } catch (\Exception $e) {
      // Do nothing
    }
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $locationTypes = ContactActionUtils::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    return new SpecificationBag(array(
      new OptionGroupSpecification('phone_type', 'phone_type', E::ts('Phone Type'), true),
      new Specification('location_type', 'Integer', E::ts('Location type'), true, $defaultLocationType, null, $locationTypes, FALSE),
    ));
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), false),
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
    return new SpecificationBag(array(
      new Specification('phone', 'String', E::ts('Phone'), false),
    ));
  }


  
}