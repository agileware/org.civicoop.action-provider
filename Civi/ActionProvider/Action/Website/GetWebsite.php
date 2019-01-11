<?php

namespace Civi\ActionProvider\Action\Website;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;

use CRM_ActionProvider_ExtensionUtil as E;

class GetWebsite extends AbstractAction {
  
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
    $existingWebsitelParams['contact_id'] = $contact_id;
    $existingWebsitelParams['website_type_id'] = $this->configuration->getParameter('website_type');
    try {
      $existingWebsite = civicrm_api3('Website', 'getsingle', $existingWebsitelParams);
      $output->setParameter('url', $existingWebsite['url']);
    } catch (\Exception $e) {
      // Do nothing
      $output->setParameter('url', '');
    }
  }
  
  /**
   * Returns the specification of the configuration options for the actual action.
   * 
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new OptionGroupSpecification('website_type', 'website_type', E::ts('Website Type'), true),
    ));
  }
  
  /**
   * Returns the specification of the parameters of the actual action.
   * 
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
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
      new Specification('url', 'String', E::ts('Url'), false),
    ));
  }
  
}