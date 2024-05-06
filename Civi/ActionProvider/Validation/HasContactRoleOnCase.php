<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Validation;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Relationship;
use CRM_ActionProvider_ExtensionUtil as E;

class HasContactRoleOnCase extends AbstractValidator {

  /**
   * Returns null when valid. When invalid return a string containing an
   * explanation message.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return string|null
   */
  protected function doValidation(ParameterBagInterface $parameters): ?string {
    $config = ConfigContainer::getInstance();
    $case_id = $parameters->getParameter('case_id');
    $contact_id = $parameters->getParameter('contact_id');
    $hasRole = false;
    if ($this->configuration->getParameter('include_client')) {
      try {
        $clientCount = \Civi\Api4\CaseContact::get(FALSE)
          ->addWhere('id', '=', $case_id)
          ->addWhere('contact_id', '=', $contact_id)
          ->execute()
          ->count();
        if ($clientCount) {
          $hasRole = true;
        }
      }
      catch (UnauthorizedException|\CRM_Core_Exception $e) {
      }
    }
    if (!$hasRole) {
      try {
        $relationshipApi = Relationship::get(FALSE)
          ->addWhere('case_id', '=', $case_id)
          ->addWhere('contact_id_b', '=', $contact_id)
          ->addWhere('is_active', '=', true);
        if ($this->getConfiguration()->doesParameterExists('relationship_type_ids') && is_array($this->getConfiguration()->getParameter('relationship_type_ids'))) {
          $relationshipApi->addWhere('relationship_type_id:name', 'IN', $this->getConfiguration()->getParameter('relationship_type_ids'));
        }
        $roleCount = $relationshipApi->execute()->count();
        if ($roleCount) {
          $hasRole = true;
        }
      }
      catch (UnauthorizedException|\CRM_Core_Exception $e) {
      }
    }

    if (!$hasRole) {
      return $this->configuration->getParameter('error_message');
    }
    return null;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */


  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $config = ConfigContainer::getInstance();
    return new SpecificationBag([
      new Specification('relationship_type_ids', 'String', E::ts('Role'), false, null, null, $config->getRelationshipTypeLabels(), true),
      new Specification('include_client', 'Boolean', E::ts('Include client'), true),
      new Specification('error_message', 'String', E::ts('Message when contact does not have a role on the case'), true)
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), true),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ]);
  }


}