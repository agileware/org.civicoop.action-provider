<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Get the employer id (and the name) for a contact. This action should be
 * obsolete
 */
class GetEmployer extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    if($parameters->doesParameterExists('contact_id')){
      $dao = \CRM_Core_DAO::executeQuery("select employer_id, organization_name from civicrm_contact where id=%1 and contact_type='Individual'",[
        1 => [$parameters->getParameter('contact_id'),'Integer']
      ]);
      if($dao->fetch()){
        $output->setParameter('employer_id', $dao->employer_id);
        $output->setParameter('employer_name', $dao->organization_name);
      }
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer',E::ts('Contact ID'), true),
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $bag = new SpecificationBag([
      new Specification('employer_id', 'Integer', E::ts('Employer ID'), true),
      new Specification('employer_name', 'Integer', E::ts('Employer Name'), true),
    ]);
    return $bag;
  }

}
