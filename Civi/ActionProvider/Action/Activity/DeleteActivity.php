<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Activity;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class DeleteActivity extends AbstractAction {

  /**
   * Returns the human readable title of this action
   */
  public function getTitle() {
    return E::ts('Delete activity');
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
    $bag = new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Activity ID'), TRUE),
    ]);
    return $bag;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag();
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    if ($parameters->doesParameterExists('id')) {
      $activityParams['id'] = $parameters->getParameter('id');

      try {
        // Do not use api as the api checks for an existing relationship.
        civicrm_api3('Activity', 'Delete', $activityParams);
      } catch (\Exception $e) {
        // Do nothing.
      }
    }
  }

  /**
   * Returns the tags for this action.
   */
  public function getTags() {
    return array(
      AbstractAction::MULTIPLE_CONTACTS_ACTION_TAG,
      AbstractAction::DATA_MANIPULATION_TAG
    );
  }



}