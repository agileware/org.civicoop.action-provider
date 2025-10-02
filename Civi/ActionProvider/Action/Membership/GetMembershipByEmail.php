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
use Civi\Api4\Contact;
use CRM_ActionProvider_ExtensionUtil as E;

class GetMembershipByEmail extends AbstractGetSingleAction {

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
    $sort_field['join_date']  = ts('Join Date');
    $sort_field['start_date'] = ts('Start Date');
    $sort_field['end_date']   = ts('End Date');

    $sort_order['ASC']  = ts('Ascending');
    $sort_order['DESC'] = ts('Descending');

    return new SpecificationBag([
      new Specification('membership_type_id', 'Integer', E::ts('Membership Type'), FALSE, NULL, 'MembershipType', NULL, TRUE),

      new Specification('status_id', 'Integer', E::ts('Status'), FALSE, NULL, 'MembershipStatus', NULL, TRUE),

      new Specification('order_by', 'String', E::ts('Select Membership, Order By'), FALSE, 'end_date', NULL, $sort_field, FALSE),

      new Specification('sort_order', 'String', E::ts('Select Membership, Sort Order'), FALSE, 'DESC', NULL, $sort_order, FALSE),
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
      new Specification('email', 'String', E::ts('E-mail address'), TRUE, NULL, NULL, NULL, FALSE),
      new Specification('membership_type_id', 'Integer', E::ts('Membership Type by Parameter'), FALSE, NULL, 'MembershipType', NULL, TRUE),
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
    return $parameters->getParameter('email');
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
    $email            = $parameters->getParameter('email');

    // Configuration membership type ids
    if ($this->configuration->doesParameterExists('membership_type_id')) {
      $apiParams['membership_type_id']  = array_filter((array) $this->configuration->getParameter('membership_type_id')); // Strips out empty strings
    }

    if ($this->configuration->doesParameterExists('status_id')) {
      $apiParams['status_id']           = array_filter((array) $this->configuration->getParameter('status_id')); // Strips out empty strings
    }

    if ($this->configuration->doesParameterExists('order_by') && !empty($this->configuration->getParameter('order_by'))) {
      $apiParams['order_by']            = $this->configuration->getParameter('order_by');
    }

    if ($this->configuration->doesParameterExists('sort_order') && !empty($this->configuration->getParameter('sort_order'))) {
      $apiParams['sort_order']          = $this->configuration->getParameter('sort_order');
    }

    // Override with parameter membership type ids
    if ($parameters->doesParameterExists('membership_type_id')) {
      $apiParams['membership_type_id']  = array_filter((array) $parameters->getParameter('membership_type_id'));
    }

    try {
      // Build the query
      $membership_query = Contact::get(FALSE)
        ->addSelect('membership.id')
        ->addJoin('Membership AS membership', 'LEFT')
        ->addWhere('email_primary.email', '=', $email)
        ->addWhere('membership.is_test', '=', FALSE);

      if ( !empty($apiParams['membership_type_id']) ) {
        $membership_query->addWhere('membership.membership_type_id', 'IN', $apiParams['membership_type_id']);
      }

      if ( isset($apiParams['status_id']) && !empty($apiParams['status_id']) ) {
        $membership_query->addWhere('membership.status_id', 'IN', $apiParams['status_id']);
      }

      if ( isset($apiParams['order_by']) && !empty($apiParams['order_by']) && isset($apiParams['sort_order']) && !empty($apiParams['sort_order']) ) {
        $membership_query->addOrderBy('membership.' . $apiParams['order_by'], $apiParams['sort_order']);
      }

      // Excute the query to get the Membership ID according to parameters and configuration
      $membership_id = $membership_query->setLimit(1)
                                        ->execute()->first()['membership.id'] ?? FALSE;

      // Get the Membership entity by ID
      if ($membership_id) {
        $membership = civicrm_api3('Membership', 'getsingle', ['id' => $membership_id]);
        $this->setOutputFromEntity($membership, $output);
      }
    }
    catch (\Exception $e) {
      // Do nothing
    }
  }

}
