<?php

namespace Civi\ActionProvider\Action\Membership;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Action\Membership\Parameter\MembershipTypeSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class ExtendOrCreateMembership extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([
      new MembershipTypeSpecification('membership_type', E::ts('Membership Type'), TRUE),
      new Specification('num_terms', 'Integer', E::ts('Number of Terms'), false),
    ]);
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag(array(
      /**
       * The parameters given to the Specification object are:
       * @param string $name
       * @param string $dataType
       * @param string $title
       * @param bool $required
       * @param mixed $defaultValue
       * @param string|null $fkEntity
       * @param array $options
       * @param bool $multiple
       */
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true, null, null, null, FALSE),
      new Specification('join_date', 'Date', E::ts('Join date'), false),
      new Specification('start_date', 'Date', E::ts('Start date'), false),
      new Specification('source', 'String', E::ts('Source'), false),
    ));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Membership');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }

    return $specs;
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification('id', 'Integer', E::ts('Membership ID')),
    ));
  }

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $membership_type = civicrm_api3('MembershipType', 'getvalue', array('name' => $this->configuration->getParameter('membership_type'), 'return' => 'id','options' => ['limit' => 1]));

    $findApiParams['contact_id'] = $parameters->getParameter('contact_id');
    $findApiParams['membership_type_id'] = $membership_type;
    $findApiParams['active_only'] = '1';
    $findApiParams['options']['limit'] = '1';
    $findApiParams['option']['sort'] = 'end_date DESC';

    // Membership not found create one.
    $apiParams = CustomField::getCustomFieldsApiParameter($parameters, $this->getParameterSpecification());
    $apiParams['contact_id'] = $parameters->getParameter('contact_id');
    $apiParams['membership_type_id'] = $membership_type;
    if ($parameters->doesParameterExists('start_date')) {
      $apiParams['start_date'] = $parameters->getParameter('start_date');
    }
    if ($parameters->doesParameterExists('end_date')) {
      $apiParams['end_date'] = $parameters->getParameter('end_date');
    }
    if ($parameters->doesParameterExists('join_date')) {
      $apiParams['join_date'] = $parameters->getParameter('join_date');
    }
    if ($parameters->doesParameterExists('source')) {
      $apiParams['source'] = $parameters->getParameter('source');
    }
    if ($this->configuration->doesParameterExists('num_terms')) {
      $apiParams['num_terms'] = $this->configuration->getParameter('num_terms');
    }

    try {
      $membership = civicrm_api3('Membership', 'getsingle', $findApiParams);
      $apiParams['id'] = $membership['id'];
      $apiParams['skipStatusCal'] = '0';
    } catch (\CiviCRM_API3_Exception $e) {

    }

    // Create or Update the event through an API call.
    try {
      $result = civicrm_api3('Membership', 'create', $apiParams);
      $output->setParameter('id', $result['id']);
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not update or create an membership.'));
    }
  }

}
