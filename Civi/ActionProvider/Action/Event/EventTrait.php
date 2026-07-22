<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

trait EventTrait {
  /**
   * @return array
   */
  public function getCampaignList(): array {
    $campaignList = [];
    try {
      $campaigns = \Civi\Api4\Campaign::get(FALSE)
        ->addSelect('id', 'title')
        ->addWhere('is_active', '=', TRUE)
        ->execute();
      foreach ($campaigns as $campaign) {
        $campaignList[$campaign['id']] = $campaign['title'];
      }
    }
    catch (\CRM_Core_Exception $ex) {
    }
    return $campaignList;
  }

  /**
   * Set campaign ID if needed:
   * - if parameter campaign_id is set, use that
   * - if parameter is not set but configuration campaign_id is set, use that
   * - else no campaign id
   *
   * @param ParameterBag $parameters
   * @param ParameterBag $configuration
   * @return int|null
   */
  public function setCampaignId(ParameterBag $parameters, ParameterBag $configuration): ?int {
    $campaignId = $parameters->getParameter('campaign_id');
    if (!$campaignId) {
      $campaignId = $configuration->getParameter('campaign_id');
    }
    return $campaignId;
  }

}
