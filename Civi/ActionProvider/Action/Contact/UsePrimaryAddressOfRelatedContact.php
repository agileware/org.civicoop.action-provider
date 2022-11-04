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

class UsePrimaryAddressOfRelatedContact extends AbstractAction {

  protected $relationshipTypes = array();
  protected $relationshipTypeIds = array();

  public function __construct() {
    parent::__construct();
    $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', array('is_active' => 1, 'options' => array('limit' => 0)));
    $this->relationshipTypes = array();
    $this->relationshipTypeIds = array();
    foreach($relationshipTypesApi['values'] as $relType) {
      $this->relationshipTypes['a_b_'.$relType['name_a_b']] = $relType['label_a_b'];
      $this->relationshipTypes['b_a_'.$relType['name_a_b']] = $relType['label_b_a'];
      $this->relationshipTypeIds['a_b_'.$relType['name_a_b']] = $relType['id'];
      $this->relationshipTypeIds['b_a_'.$relType['name_b_a']] = $relType['id'];
    }
  }

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

    $relationship_type_ids = $this->configuration->getParameter('relationship_type_id');
    foreach($relationship_type_ids as $relationship_type_id) {
      $dir = substr($relationship_type_id, 0, 4);
      $relationshipParams = array();
      $relationshipParams['relationship_type_id'] = $this->relationshipTypeIds[$relationship_type_id];
      $relationshipParams['is_active'] = 1;
      if ($dir == 'a_b_') {
        $relationshipParams['contact_id_a'] = $contact_id;
        $relationshipParams['return'] = 'contact_id_b';
      } else {
        $relationshipParams['contact_id_b'] = $contact_id;
        $relationshipParams['return'] = 'contact_id_a';
      }
      try {
        $master_contact_id = civicrm_api3('Relationship', 'getvalue', $relationshipParams);
        break;
      } catch (\CiviCRM_API3_Exception $ex) {
        // Do nothing
      }
    }

    if (!$master_contact_id) {
      return;
    }

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
      $addressParams['update_current_employer'] = '0'; // Do not create relationships when we add the shared address.
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
    $specs = new SpecificationBag([
      new Specification('relationship_type_id', 'String', E::ts('Relationship type'), true, null, null, $this->relationshipTypes, true),
    ]);

    $locationTypes = ContactActionUtils::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);
    $specs->addSpecification(new Specification('location_type', 'Integer', E::ts('Location type'), true, $defaultLocationType, null, $locationTypes, FALSE));
    $specs->addSpecification(new Specification('update_existing', 'Boolean', E::ts('Update existing'), false, 0, null, null, FALSE));
    return $specs;
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $contact_id = new Specification('contact_id', 'Integer', E::ts('Contact ID'), false);
    $specs = new SpecificationBag(array(
      $contact_id
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
