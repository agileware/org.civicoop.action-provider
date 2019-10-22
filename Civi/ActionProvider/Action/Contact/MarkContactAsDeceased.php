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

class MarkContactAsDeceased extends AbstractAction {

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
    // If we don't add the contact type parameters
    // the is deceased action will also reset the contact sub type.
    $params['contact_type'] = $contact['contact_type'];
    $params['contact_sub_type'] = $contact['contact_sub_type'];
    $params['is_deceased'] = 1;
    if ($parameters->doesParameterExists('deceased_date')){
      $params['deceased_date'] = $parameters->getParameter('deceased_date');
    }
    civicrm_api3('Contact', 'Create', $params);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('deceased_date', 'Date', E::ts('Deceased date'), false),
    ));
  }


}
