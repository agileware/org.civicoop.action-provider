<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\AbstractGetSingleAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\Api4\Contribution;

use CRM_ActionProvider_ExtensionUtil as E;
 

class GetContributionByMembershipID extends AbstractGetSingleAction {

  protected function getApiEntity(): string {
    return 'Contribution';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('contribution_id');
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $sort_field['receive_date']  = ts('Receive Date');
    $sort_field['id']  = ts('Contribution ID');

    $sort_order['ASC']  = ts('Ascending');
    $sort_order['DESC'] = ts('Descending');

    return new SpecificationBag([
      new OptionGroupSpecification('contribution_status', 'contribution_status', E::ts('Status of contribution'), FALSE, NULL, TRUE),
      new Specification('order_by', 'String', E::ts('Select Contribution, Order By'), FALSE, 'receive_date', NULL, $sort_field, FALSE),
      new Specification('sort_order', 'String', E::ts('Select Contribution, Sort Order'), FALSE, 'DESC', NULL, $sort_order, FALSE),
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
      new Specification('membership_id', 'Integer', E::ts('Membership ID'), TRUE, NULL, 'Membership', NULL, FALSE),
    ]);

    return $specs;
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
    $apiParams['membership_id']            = $parameters->getParameter('membership_id');

    // Configuration
    if ($this->configuration->doesParameterExists('contribution_status') && !empty($this->configuration->getParameter('contribution_status'))) {
      $apiParams['contribution_status']           = array_filter((array) $this->configuration->getParameter('contribution_status')); // Strips out empty strings
    }

    if ($this->configuration->doesParameterExists('order_by') && !empty($this->configuration->getParameter('order_by'))) {
      $apiParams['order_by']            = $this->configuration->getParameter('order_by');
    }

    if ($this->configuration->doesParameterExists('sort_order') && !empty($this->configuration->getParameter('sort_order'))) {
      $apiParams['sort_order']          = $this->configuration->getParameter('sort_order');
    }

    try {
      // Build the query
      $contributions_query = Contribution::get(FALSE)
                                 ->addJoin('LineItem AS line_item', 'LEFT', ['line_item.contribution_id', '=', 'id'])
                                 ->addJoin('Membership AS membership', 'LEFT', ['line_item.entity_id', '=', 'membership.id'])
                                 ->addWhere('line_item.entity_table', '=', 'civicrm_membership')
                                 ->addWhere('is_test', '=', FALSE);;

      if ( !empty($apiParams['membership_id']) ) {
        $contributions_query->addWhere('membership.id', '=', $apiParams['membership_id']);
      }

      if ( !empty($apiParams['contribution_status']) ) {
        $contributions_query->addWhere('contribution_status_id', 'IN', $apiParams['contribution_status']);
      }

      if ( isset($apiParams['order_by']) && !empty($apiParams['order_by']) && isset($apiParams['sort_order']) && !empty($apiParams['sort_order']) ) {
        $contributions_query->addOrderBy($apiParams['order_by'], $apiParams['sort_order']);
      }

      // Execute the query to get the Contribution ID according to parameters and configuration
      $contribution_id = $contributions_query->execute() ?? FALSE;
      
      if ($contribution_id) {
        $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $contribution_id[0]['id']]);
        $this->setOutputFromEntity($contribution, $output);
      }
    }
    catch (\Exception $e) {
      // Do nothing
    }
  }

}
