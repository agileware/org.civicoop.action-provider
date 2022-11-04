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

use CRM_ActionProvider_ExtensionUtil as E;

class UsePrimaryAddressOfContact extends AbstractAction {

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
    $existingAddressId = false;
    $is_create_relationships = $this->configuration->getParameter('create_relationships');
    if ($this->configuration->getParameter('update_existing')) {
      // Try to find existing address
      $existingAddressParams['contact_id'] = $contact_id;
      $existingAddressParams['location_type_id'] = $this->configuration->getParameter('location_type');
      $existingAddressParams['return'] = 'id';
      try {
        $existingAddressId = civicrm_api3('Address', 'getvalue', $existingAddressParams);
      } catch (\Exception $e) {
        // Do nothing
      }
    }

    // Find address of contact
    $master_contact_id = $parameters->getParameter('master_contact_id');
    try {
      $master_address = civicrm_api3('Address', 'getsingle', [
        'contact_id' => $master_contact_id,
        'is_primary' => 1
      ]);

      // First reset all address values.
      $addressParams['name'] = '';
      $addressParams['street_address'] = '';
      $addressParams['supplemental_address_1'] = '';
      $addressParams['supplemental_address_2'] = '';
      $addressParams['postal_code'] = '';
      $addressParams['city'] = '';
      $addressParams['country_id'] = '';

      // Now copy the values from the master address.
      $doNotProcess = array('contact_id', 'id', 'location_type_id', 'is_primary');
      foreach($master_address as $key => $val) {
        if (!in_array($key, $doNotProcess)) {
          $addressParams[$key] = $val;
        }
      }
      if ($existingAddressId) {
        $addressParams['id'] = $existingAddressId;
      }
      $addressParams['contact_id'] = $contact_id;
      $addressParams['master_id'] = $master_address['id'];
      $addressParams['location_type_id'] = $this->configuration->getParameter('location_type');
      $addressParams['update_current_employer'] = $is_create_relationships ? '1' : '0';
      if (empty($existingAddressId) || $this->configuration->getParameter('update_existing')) {
        $result = civicrm_api3('Address', 'create', $addressParams);
        $output->setParameter('id', $result['id']);
      }
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
    $specs = new SpecificationBag();
    $locationTypes = ContactActionUtils::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    $specs->addSpecification(new Specification('location_type', 'Integer', E::ts('Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $specs->addSpecification(new Specification('update_existing', 'Boolean', E::ts('Update existing'), false, 0, null, null, FALSE));
    $createRelationshipSpec = new Specification('create_relationships', 'Boolean', E::ts('Address: Automatically create relationships'), false, true, null, null, FALSE);
    $createRelationshipSpec->setDescription(E::ts('CiviCRM can create relationships automatically when you create an shared address. Such as household member, employer of etc.'));
    $specs->addSpecification($createRelationshipSpec);
    return $specs;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $contact_id = new Specification('contact_id', 'Integer', E::ts('Contact ID'), false);
    $master_contact_id = new Specification('master_contact_id', 'Integer', E::ts('Master Contact ID'), false);
    $master_contact_id->setDescription(E::ts('This is the contact from which the primary address is going to be used.'));
    $specs = new SpecificationBag(array(
      $contact_id,
      $master_contact_id
    ));
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
