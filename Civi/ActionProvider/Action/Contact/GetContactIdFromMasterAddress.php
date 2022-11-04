<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetContactIdFromMasterAddress extends AbstractAction {

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
    $existingAddressParams['contact_id'] = $contact_id;
    $existingAddressParams['location_type_id'] = $this->configuration->getParameter('location_type_id');
    try {
      $existingAddress = civicrm_api3('Address', 'getsingle', $existingAddressParams);
      if (isset($existingAddress['master_id']) && !empty($existingAddress['master_id'])) {
        $output->setParameter('contact_id', $this->getContactIdFromAddress($existingAddress['master_id']));
      }
    } catch (\Exception $e) {
      // Do nothing
    }
  }

  protected function getContactIdFromAddress($address_id) {
    $existingAddress = civicrm_api3('Address', 'getsingle', array('id' => $address_id));
    if (isset($existingAddress['master_id']) && !empty($existingAddress['master_id'])) {
      return $this->getContactIdFromAddress($existingAddress['master_id']);
    }
    return $existingAddress['contact_id'];
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
      new Specification('location_type_id', 'Integer', E::ts('Location type'), true, $defaultLocationType, null, $locationTypes, FALSE)
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
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), false),
    ));
  }

}