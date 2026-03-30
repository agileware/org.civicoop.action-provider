<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Action\Contact\ContactActionUtils;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateAddressById extends AbstractAction {
  
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
    $existingAddressId = $parameters->getParameter('address_id');
    $id = ContactActionUtils::createAddress($existingAddressId, $contact_id, $parameters, $this->configuration);
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
    $specs->removeSpecification($specs->getSpecificationByName('address_update_existing'));
    return $specs;
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      new Specification('address_id', 'Integer', E::ts('Address ID'), true),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ));
    ContactActionUtils::createAddressParameterSpecification($specs);
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
      new Specification('id', 'Integer', E::ts('Address ID')),
    ));
  }
  
}