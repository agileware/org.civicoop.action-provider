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

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Contribution;
use CRM_ActionProvider_ExtensionUtil as E;

class LinkContributionToContributionPage extends AbstractAction {

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
    $contributionPageId = null;
    if ($parameters->getParameter('contribution_page_id')) {
      $contributionPageId = $parameters->getParameter('contribution_page_id');
    } elseif ($this->configuration->getParameter('contribution_page_id')) {
      $contributionPageId = $this->configuration->getParameter('contribution_page_id');
    }
    if (!empty($contributionPageId)) {
      try {
        Contribution::update(FALSE)
          ->addValue('contribution_page_id', $contributionPageId)
          ->addWhere('id', '=', $parameters->getParameter('contribution_id'))
          ->execute();
      }
      catch (UnauthorizedException|\CRM_Core_Exception $e) {
      }
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
      new Specification('contribution_page_id', 'Integer', E::ts('Contribution Page ID'), false, null, 'ContributionPage')
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('contribution_page_id', 'Integer', E::ts('Contribution Page ID'), false),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), true),
    ));
  }


}
