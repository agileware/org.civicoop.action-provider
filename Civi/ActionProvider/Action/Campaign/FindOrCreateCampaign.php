<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Campaign;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class FindOrCreateCampaign extends AbstractAction {

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
    $findParams['campaign_type_id'] = $this->configuration->getParameter('type');
    $findParams['title'] = $parameters->getParameter('title');
    $find = civicrm_api3('Campaign', 'get', $findParams);
    if ($find['count'] > 0) {
      $campaign = reset($find['values']);
      $output->setParameter('id', $campaign['id']);
    } else {
      $createParams['campaign_type_id'] = $this->configuration->getParameter('type');
      $createParams['title'] = $parameters->getParameter('title');
      if ($parameters->doesParameterExists('description')) {
        $createParams['description'] = $parameters->getParameter('description');
      }
      $result = civicrm_api3('Campaign', 'create', $createParams);
      $output->setParameter('id', $result['id']);
    }
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $campaignTypeApi = civicrm_api3('OptionValue', 'get', array(
      'option_group_id' => "campaign_type",
      'options' => array('limit' => 0)
    ));
    $campaignTypes = array();
    foreach($campaignTypeApi['values'] as $campaignType) {
      $campaignTypes[$campaignType['name']] = $campaignType['label'];
    }

    return new SpecificationBag(array(
      new Specification('type', 'String', E::ts('Campaign type'), true, null, null, $campaignTypes),
    ));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification('title', 'String', E::ts('Title'), true),
      new Specification('description', 'String', E::ts('Description'), false),
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
      new Specification('id', 'Integer', E::ts('Integer')),
    ));
  }


}
