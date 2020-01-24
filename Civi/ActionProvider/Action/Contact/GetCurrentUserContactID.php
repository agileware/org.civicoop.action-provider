<?php

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * This action let's you retrieve the currently logged in user
 */
class GetCurrentUserContactID extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
        new Specification('fallback_contact_id','Integer', E::ts('Fallback Contact'), false),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag([]);
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
        new Specification('contact_id', 'Integer', E::ts('Contact ID')),
    ]);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // the default way of getting the contact is this:
    $contactID = \CRM_Core_Session::getLoggedInContactID();

    // if that didn't work, maybe it came through the REST API:
    if (empty($contactID)) {
      $api_key = \CRM_Utils_Request::retrieve('api_key', 'String');
      if ($api_key) {
        $contactID = \CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_Contact', $api_key, 'id', 'api_key');
      }
    }

    // if still empty - set the fallback
    if (empty($contactID)) {
      $contactID = $this->configuration->getParameter('fallback_contact_id');
    }

    // fix getLoggedInContactID, otherwise some processes might fail, e.g. activity creation
    if ($contactID != \CRM_Core_Session::getLoggedInContactID()) {
      \CRM_Core_Session::singleton()->set('userID', $contactID);
    }

    // return our result
    $output->setParameter('contact_id', $contactID);
  }
}