<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Membership;

use \Civi\ActionProvider\Action\AbstractGetSingleAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\Api4\Membership;
use CRM_ActionProvider_ExtensionUtil as E;

class GetMembershipByIdWithConfig extends AbstractGetSingleAction {

  protected function getApiEntity(): string {
    return 'Membership';
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    return new SpecificationBag([
      new Specification('membership_type_id', 'Integer', E::ts('Membership Type'), FALSE, NULL, 'MembershipType', NULL, TRUE),

      new Specification('status_id', 'Integer', E::ts('Status'), FALSE, NULL, 'MembershipStatus', NULL, TRUE),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification(): SpecificationBag {
    $specs = new SpecificationBag([
      new Specification('membership_id', 'Integer', E::ts('Membership ID'), TRUE, NULL, NULL, NULL, FALSE),
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), FALSE, NULL, NULL, NULL, FALSE),
    ]);

    return $specs;
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param   \Civi\ActionProvider\Parameter\ParameterBagInterface  $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters): int {
    return $parameters->getParameter('membership_id');
  }

  /**
   * Run the action
   *
   * @param   ParameterBagInterface  $parameters
   *   The parameters to this action.
   * @param   ParameterBagInterface  $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output): void {
    $apiParams['membership_id'] = $parameters->getParameter('membership_id');

    // Configuration membership type ids
    if ($this->configuration->parameterExists('membership_type_id')) {
      $apiParams['membership_type_id'] = array_filter((array) $this->configuration->getParameter('membership_type_id')); // Strips out empty strings
    }

    if ($this->configuration->parameterExists('status_id')) {
      $apiParams['status_id'] = array_filter((array) $this->configuration->getParameter('status_id')); // Strips out empty strings
    }

    // Override with parameter membership id
    if ($parameters->parameterExists('membership_id')) {
      $apiParams['membership_id'] = $parameters->getParameter('membership_id');
    }

    if ($parameters->parameterExists('contact_id')) {
      $apiParams['contact_id'] = $parameters->getParameter('contact_id');
    }

    try {
      // Build the query
      $membership_query = Membership::get(FALSE)
                                    ->addSelect('id')
                                    ->addWhere('id', '=', $apiParams['membership_id'])
                                    ->addWhere('is_test', '=', FALSE);

      if ( !empty($apiParams['membership_type_id']) ) {
        $membership_query->addWhere('membership_type_id', 'IN', $apiParams['membership_type_id']);
      }

      if ( !empty($apiParams['status_id']) ) {
        $membership_query->addWhere('status_id', 'IN', $apiParams['status_id']);
      }

      if ( !empty($apiParams['contact_id']) ) {
        $membership_query->addWhere('contact_id', '=', $apiParams['contact_id']);
      }

      // Excute the query to get the Membership ID according to parameters and configuration
      $membership_id = $membership_query->setLimit(1)
                                        ->execute()->first()['id'] ?? FALSE;

      // Get the Membership entity by ID
      if ($membership_id) {
        $membership = civicrm_api3('Membership', 'getsingle', ['id' => $membership_id]);
        $this->setOutputFromEntity($membership, $output); // Expects a v3 Entity, so we have to do this
      }
    }
    catch (\Exception $e) {
      // Do nothing
    }
  }

}
