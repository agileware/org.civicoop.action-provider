<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class ShareAddress extends AbstractAction {

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
    $master_address_id = $parameters->getParameter('master_address_id');
    $contact_id = $parameters->getParameter('contact_id');
    if (empty($master_address_id) || empty($contact_id)) {
      return;
    }

    $location_type_id = $this->configuration->getParameter('address_location_type');
    $is_primary = $this->configuration->getParameter('address_is_primary');
    $is_create_relationships = $this->configuration->getParameter('create_relationships');
    $existingAddressId = ContactActionUtils::findExistingAddress($contact_id, $location_type_id , $is_primary);
    if ($this->configuration->getParameter('address_update_existing') || empty($existingAddressId)) {
      $addressParams = civicrm_api3('Address', 'getsingle', ['id' => $master_address_id]);
      unset($addressParams['id']);
      unset($addressParams['contact_id']);
      if ($existingAddressId) {
        $addressParams['id'] = $existingAddressId;
      }
      $addressParams['contact_id'] = $contact_id;
      $addressParams['master_id'] = $master_address_id;
      $addressParams['location_type_id'] = $location_type_id;
      $addressParams['is_primary'] = $is_primary ? '1': '0';
      $addressParams['update_current_employer'] = $is_create_relationships ? '1' : '0';
      $result = civicrm_api3('Address', 'create', $addressParams);
      $output->setParameter('id', $result['id']);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $specs = new SpecificationBag();
    ContactActionUtils::createAddressConfigurationSpecification($specs);
    $createRelationshipSpec = new Specification('create_relationships', 'Boolean', E::ts('Address: Automatically create relationships'), false, 0, null, null, FALSE);
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
    $specs = new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), false),
      new Specification('master_address_id', 'Integer', E::ts('Master Address ID'), false),
    ));
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

}
