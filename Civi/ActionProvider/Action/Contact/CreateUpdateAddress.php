<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Action\Contact\ContactActionUtils;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateUpdateAddress extends AbstractAction {
  
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
    $id = ContactActionUtils::createAddress($contact_id, $parameters, $this->configuration);
    $output->setParameter('id', $id);
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $specs = new SpecificationBag();
    ContactActionUtils::createAddressConfigurationSpecification($specs);
    return $specs;
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag();
    ContactActionUtils::createAddressParameterSpecification($specs);
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
      new Specification('id', 'Integer', E::ts('Address ID')),
    ));
  }
  
  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Create or update address of a contact');
  }
  
  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::SINGLE_CONTACT_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG,
    );
  }
  
}