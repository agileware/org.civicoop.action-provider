<?php

namespace Civi\ActionProvider\Action\Contact;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class FindIndividualByNameAndEmail extends AbstractAction {
	
	/**
	 * Run the action
	 * 
	 * @param ParameterInterface $parameters
	 *   The parameters to this action.
	 * @param ParameterBagInterface $output
	 * 	 The parameters this action can send back 
	 * @return void
	 */
	protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $sql = "
      SELECT contact.id  AS id, 10 as weight 
      FROM civicrm_contact as contact
      INNER JOIN civicrm_email email ON contact.id = email.contact_id
      WHERE contact.is_deleted = 0 AND contact.is_deceased = 0 AND contact.contact_type = 'Individual'
      AND email.email = %1
      AND contact.first_name = %2 AND contact.last_name = %3
      
      UNION SELECT contact.id AS id, 20 as weight 
      FROM civicrm_contact as contact
      INNER JOIN civicrm_email email ON contact.id = email.contact_id
      WHERE contact.is_deleted = 0 AND contact.is_deceased = 0 AND contact.contact_type = 'Individual'
      AND email.email = %1
      AND contact.last_name = %3
      
      UNION SELECT contact.id AS id, 30 as weight 
      FROM civicrm_contact as contact
      INNER JOIN civicrm_email email ON contact.id = email.contact_id
      WHERE contact.is_deleted = 0 AND contact.is_deceased = 0 AND contact.contact_type = 'Individual'
      AND email.email = %1
      AND contact.first_name = %2
      
      UNION SELECT contact.id AS id, 40 as weight 
      FROM civicrm_contact as contact
      INNER JOIN civicrm_email email ON contact.id = email.contact_id AND email.is_primary = 1
      WHERE contact.is_deleted = 0 AND contact.is_deceased = 0 AND contact.contact_type = 'Individual'
      AND email.email = %1
      
      UNION SELECT contact.id AS id, 50 as weight 
      FROM civicrm_contact as contact
      WHERE contact.is_deleted = 0 AND contact.is_deceased = 0 AND contact.contact_type = 'Individual'
      AND contact.first_name = %2 AND contact.last_name = %3
      
      ORDER BY weight ASC
    ";

    $sqlParams[1] = array($parameters->getParameter('email'), 'String');
    $sqlParams[2] = array($parameters->getParameter('first_name'), 'String');
    $sqlParams[3] = array($parameters->getParameter('last_name'), 'String');

    $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
    if ($dao->fetch()) {
      $output->setParameter('contact_id', $dao->id);
    }
	}
	
	/**
	 * Returns the specification of the configuration options for the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {	
		return new SpecificationBag(array());
	}
	
	/**
	 * Returns the specification of the parameters of the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getParameterSpecification() {
		return new SpecificationBag(array(
			new Specification('first_name', 'String', E::ts('First name'), true),
      new Specification('last_name', 'String', E::ts('Last name'), true),
      new Specification('email', 'String', E::ts('E-mail'), false),
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
			new Specification('contact_id', 'Integer', E::ts('Contact ID'), true)
		));
	}
	
}
