<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contact;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class SetContactSubType extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $contact_id = $parameters->getParameter('contact_id');
    $contact = civicrm_api3('Contact', 'getsingle', array('id' => $contact_id));
    $params['id'] = $contact_id;
    $params['contact_type'] = $contact['contact_type'];
    $params['contact_sub_type'] = $contact['contact_sub_type'];
    $contact_sub_type = $this->configuration->getParameter('contact_sub_type');
    if ($contact_sub_type) {
      $params['contact_sub_type'][] = $contact_sub_type;
      civicrm_api3('Contact', 'Create', $params);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $contactSubTypes = array();
    $contactSubTypesApi = civicrm_api3('ContactType', 'get', array('options' => array('limit' => 0)));
    $contactSubTypes[''] = E::ts(' - Select - ');
    foreach($contactSubTypesApi['values'] as $contactSubType) {
      if (isset($contactSubType['parent_id']) && $contactSubType['parent_id']) {
        $contactSubTypes[$contactSubType['name']] = $contactSubType['label'];
      }
    }

    return new SpecificationBag(array(
      new Specification('contact_sub_type', 'String', E::ts('Contact sub type'), false, null, null, $contactSubTypes, FALSE),
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


}
