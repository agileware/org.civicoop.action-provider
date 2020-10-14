<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;

use CRM_ActionProvider_ExtensionUtil as E;

class LinkContributionToParticipant extends AbstractAction {

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
    $apiParams['contribution_id'] = $parameters->getParameter('contribution_id');
    $apiParams['participant_id'] = $parameters->getParameter('participant_id');
    civicrm_api3('ParticipantPayment', 'create', $apiParams);

    if ($this->configuration->getParameter('addLineItems')) {
      $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $parameters->getParameter('contribution_id')]);
      $participant = civicrm_api3('Participant', 'getsingle', ['id' => $parameters->getParameter('participant_id')]);
      $event = civicrm_api3('Event', 'getsingle', ['id' => $participant['event_id']]);
      $lineItemParams['entity_table'] = 'civicrm_participant';
      $lineItemParams['entity_id'] = $parameters->getParameter('participant_id');
      $lineItemParams['participant_count'] = 1;
      $lineItemParams['label'] = $event['title'];
      $lineItemParams['contribution_id'] = $parameters->getParameter('contribution_id');
      $lineItemParams['qty'] = 1;
      $lineItemParams['unit_price'] = $contribution['total_amount'];
      $lineItemParams['line_total'] = $contribution['total_amount'];
      $lineItemParams['financial_type_id'] = $contribution['financial_type_id'];
      $result = civicrm_api3('LineItem', 'create', $lineItemParams);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(array(
      new Specification('addLineItems', 'Boolean', E::ts('Add Line Item'), true, false),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), true),
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), true),
    ));
  }


}
