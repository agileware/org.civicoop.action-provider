<?php
/**
 * @author Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Campaign;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class GetCampaign extends AbstractAction {
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
    $findParams['id'] = $parameters->getParameter('id');

    $spec = $this->getOutputSpecification();
    /** @var Specification $s */
    foreach($spec->getIterator() as $s) {
      $findParams['return'][] = $s->getName();
    }
    $find = civicrm_api3('Campaign', 'get', $findParams);
    if ($find['count'] > 0) {
      $campaign = reset($find['values']);
      $output->setParameter('campaign_id', $campaign['id']);
      foreach($campaign as $key => $value) {
        if ($spec->getSpecificationByName($key)) {
          $output->setParameter($key, $value);
        }
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
    return new SpecificationBag();
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Campaign ID'), TRUE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $specs = new SpecificationBag();
    $specs->addSpecification(new Specification('id', 'Integer', E::ts('Campaign ID'), TRUE));
    $specs->addSpecification(new Specification('title', 'String', E::ts('Title'), FALSE));
    $specs->addSpecification(new Specification('description', 'String', E::ts('Description'), FALSE));
    $specs->addSpecification(new Specification('start_date', 'Date', E::ts('Start Date'), FALSE));
    $specs->addSpecification(new Specification('end_date', 'Date', E::ts('End Date'), FALSE));
    $specs->addSpecification(new Specification('is_active', 'Boolean', E::ts('Is Active'), FALSE));
    $specs->addSpecification(new OptionGroupSpecification('campaign_type_id', 'campaign_type', E::ts('Campaign Type'), FALSE));
    $specs->addSpecification(new OptionGroupSpecification('status_id', 'campaign_status', E::ts('Campaign Status'), FALSE));
    $specs->addSpecification(new Specification('external_identifier', 'String', E::ts('External Identifier'), FALSE));
    $specs->addSpecification(new Specification('parent_id', 'Integer', E::ts('Parent ID'), FALSE));
    $specs->addSpecification(new Specification('goal_general', 'String', E::ts('Goal General'), FALSE));
    $specs->addSpecification(new Specification('goal_revenue', 'Integer', E::ts('Goal Revenue'), FALSE));
    return $specs;
  }


}
