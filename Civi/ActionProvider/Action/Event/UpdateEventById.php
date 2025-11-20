<?php

namespace Civi\ActionProvider\Action\Event;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use Civi\ActionProvider\Exception\ExecutionException;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use Civi\ActionProvider\Utils\CustomField;
use Civi\Api4\Event;

use CRM_ActionProvider_ExtensionUtil as E;

class UpdateEventById extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag([]);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $specs = new SpecificationBag([
      /**
       * The parameters given to the Specification object are:
       *
       * @param string $name
       * @param string $dataType
       * @param string $title
       * @param bool $required
       * @param mixed $defaultValue
       * @param string|null $fkEntity
       * @param array $options
       * @param bool $multiple
       */
      new Specification('event_id', 'Integer', E::ts('Event ID'), TRUE, NULL, NULL, NULL, FALSE),
      new OptionGroupSpecification('event_type', 'event_type', E::ts('Event Type'), FALSE),
      new Specification('title', 'String', E::ts('Title'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('description', 'String', E::ts('Description'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('summary', 'String', E::ts('Summary'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('start_date', 'Timestamp', E::ts('Start date'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('end_date', 'Timestamp', E::ts('End date'), FALSE, NULL, NULL, NULL, FALSE),
      new Specification('is_active', 'Boolean', E::ts('Is active'), FALSE, 1, NULL, NULL, FALSE),
      new Specification('is_public', 'Boolean', E::ts('Is public'), FALSE, 0, NULL, NULL, FALSE),
      new Specification('max_participants', 'Integer', E::ts('Max. Participants'), FALSE, NULL),
      new Specification('event_full_text', 'String', E::ts('Text when event is full'), FALSE, NULL),
      new Specification('waitlist_text', 'String', E::ts('Waitlist Text'), FALSE, NULL),
      new Specification('confirm_from_name', 'String', E::ts('Confirm From Name'), FALSE),
      new Specification('confirm_from_email', 'String', E::ts('Confirm From Email'), FALSE),
    ]);

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Event');
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
    return new SpecificationBag([
      new Specification('id', 'Integer', E::ts('Event ID')),
    ]);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Civi\ActionProvider\Exception\ExecutionException
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $apiParams = CustomField::getCustomFieldsApi4Parameter($parameters, $this->getParameterSpecification());

    $id = $parameters->getParameter('event_id');

    if ($parameters->parameterExists('title')) {
      $apiParams['title'] = $parameters->getParameter('title');
    }
    if ($parameters->parameterExists('description')) {
      $apiParams['description'] = $parameters->getParameter('description');
    }
    if ($parameters->parameterExists('summary')) {
      $apiParams['summary'] = $parameters->getParameter('summary');
    }
    if ($parameters->parameterExists('start_date')) {
      $apiParams['start_date'] = $parameters->getParameter('start_date');
    }
    if ($parameters->parameterExists('end_date')) {
      $apiParams['end_date'] = $parameters->getParameter('end_date');
    }
    if ($parameters->parameterExists('event_type_id')) {
      $apiParams['event_type_id'] = $parameters->getParameter('event_type');
    }
    if ($parameters->parameterExists('is_active')) {
      $apiParams['is_active'] = $parameters->getParameter('is_active');
    }
    if ($parameters->parameterExists('is_public')) {
      $apiParams['is_public'] = $parameters->getParameter('is_public');
    }
    if ($parameters->parameterExists('max_participants')) {
      $apiParams['max_participants'] = $parameters->getParameter('max_participants');
    }
    if ($parameters->parameterExists('event_full_text')) {
      $apiParams['event_full_text'] = $parameters->getParameter('event_full_text');
    }
    if ($parameters->parameterExists('waitlist_text')) {
      $apiParams['waitlist_text'] = $parameters->getParameter('waitlist_text');
    }
    if ($parameters->parameterExists('confirm_from_name')) {
      $apiParams['confirm_from_name'] = $parameters->getParameter('confirm_from_name');
    }
    if ($parameters->parameterExists('confirm_from_email')) {
      $apiParams['confirm_from_email'] = $parameters->getParameter('confirm_from_email');
    }

    // Update the event through an API call.
    try {
      $result = Event::update(FALSE)
        ->setValues($apiParams)
        ->addWhere('id', '=', $id)
        ->execute();

      if (isset($result->single()['id'])) {
        $output->setParameter('id', $result->single()['id']);
      }
    }
    catch (\Throwable $e) {
      throw new ExecutionException(E::ts('Could not update the event:') . ' ' . $e->getMessage() );
    }
  }

}
