<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Relationship;
use CRM_ActionProvider_ExtensionUtil as E;

class HasContactRoleOnCase extends AbstractAction {

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
    $case_id = $parameters->getParameter('case_id');
    $contact_id = $parameters->getParameter('contact_id');
    $hasRole = false;
    if ($this->configuration->getParameter('include_client')) {
      try {
        $clientCount = \Civi\Api4\CaseContact::get(FALSE)
          ->addWhere('case_id', '=', $case_id)
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

    if ($hasRole) {
      $output->setParameter('contact_id', $contact_id);
    } elseif ($this->configuration->getParameter('fail_when_not_found')) {
      throw new ExecutionException(E::ts('Contact has no role on the case'));
    }
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
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ]);
  }


  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $config = ConfigContainer::getInstance();
    $include_client = new Specification('include_client', 'Boolean', E::ts('Include client'), true);
    $include_client->setDescription(E::ts('Set to yes when you want to check on any role or client.'));
    return new SpecificationBag([
      new Specification('relationship_type_ids', 'String', E::ts('Role'), false, null, null, $config->getRelationshipTypeLabels(), true),
      $include_client,
      new Specification('fail_when_not_found', 'Boolean', E::ts('Fail when not found'), true)
    ]);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), true),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
    ]);
  }


}
