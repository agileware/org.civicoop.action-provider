<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\ConfigContainer;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Action\Contact\ContactActionUtils;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateEventFromTemplate extends AbstractAction {

 /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {

    $locationTypes = ContactActionUtils::getLocationTypes();
    reset($locationTypes);
    $defaultLocationType = key($locationTypes);

    return new SpecificationBag(array(
      new Specification('template_id', 'Integer', E::ts('Event Template'), false, null, 'Event', null, FALSE),
      new Specification('add_address', 'Boolean', E::ts('Add address'), true, false),
      new Specification('address_location_type', 'Integer', E::ts('Address: Location type'), false, $defaultLocationType, null, $locationTypes, FALSE),
    ));
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
      new Specification('template_id', 'Integer', E::ts('Event Template'), true, null, 'Event', null, FALSE),

      new OptionGroupSpecification('event_type', 'event_type', E::ts('Event Type'), false),

      new Specification('title', 'String', E::ts('Title'), true, null, null, null, FALSE),
      new Specification('summary', 'String', E::ts('Summary'), false, null, null, null, FALSE),
      new Specification('description', 'String', E::ts('Description'), false, null, null, null, FALSE),

      new Specification('start_date', 'Timestamp', E::ts('Start date'), true, null, null, null, FALSE),
      new Specification('end_date', 'Timestamp', E::ts('End date'), false, null, null, null, FALSE),

      new Specification('is_active', 'Boolean', E::ts('Is active'), false, 1, null, null, FALSE),
      new Specification('is_public', 'Boolean', E::ts('Is public'), false, 0, null, null, FALSE),

      new Specification('organiser', 'Integer', E::ts('Event Organiser'), false, null, 'Contact', null, FALSE),

      new Specification('campaign_id', 'Integer', E::ts('Campaign ID'), false, null, 'Campaign', null, FALSE),

    ));

    $config = ConfigContainer::getInstance();
    $customGroups = $config->getCustomGroupsForEntity('Event');
    foreach ($customGroups as $customGroup) {
      if (!empty($customGroup['is_active'])) {
        $specs->addSpecification(CustomField::getSpecForCustomGroup($customGroup['id'], $customGroup['name'], $customGroup['title']));
      }
    }

    ContactActionUtils::createAddressParameterSpecification($specs);
    return $specs;
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
      new Specification('id', 'Integer', E::ts('Event ID')),
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
    $existingAddressId = false;
    $locBlockId = false;
    if ($this->configuration->getParameter('add_address')) {
      if ($parameters->doesParameterExists('event_id')) {
        try {
          $event = civicrm_api3('Event', 'getsingle', ['id' => $parameters->getParameter('event_id')]);
          $locationInUseByOtherEvents = civicrm_api3('Event', 'getcount', array('loc_block_id' => $event['loc_block_id']));
          if ($locationInUseByOtherEvents == 1) {
            $loc = civicrm_api3('LocBlock', 'getsingle', ['id' => $event['loc_block_id']]);
            $locBlockId = $loc['id'];
            $existingAddressId = $loc['address_id'];
          }
        } catch (\Exception $e) {
          // Do nothing
        }
      }
      $address_id = ContactActionUtils::createAddress($existingAddressId, null, $parameters, $this->configuration);
      if (!$locBlockId) {
        $result = civicrm_api3('LocBlock', 'create', array('address_id' => $address_id));
        $locBlockId = $result['id'];
      }
    }

    $apiParams = array();

    $apiParams['template_id'] = $parameters->getParameter('template_id');

    $apiParams['title'] = $parameters->getParameter('title');

    $apiParams['start_date'] = $parameters->getParameter('start_date');

    if ($parameters->doesParameterExists('event_type')) {
      $apiParams['event_type_id'] = $parameters->getParameter('event_type');
    }

    if ($parameters->doesParameterExists('summary')) {
      $apiParams['summary'] = $parameters->getParameter('summary');
    }

    if ($parameters->doesParameterExists('description')) {
      $apiParams['description'] = $parameters->getParameter('description');
    }

    if ($parameters->doesParameterExists('campaign_id')) {
      $apiParams['campaign_id'] = $parameters->getParameter('campaign_id');
    }

    if ($parameters->doesParameterExists('end_date')) {
      $apiParams['end_date'] = $parameters->getParameter('end_date');
    }

    if ($locBlockId) {
      $apiParams['loc_block_id'] = $locBlockId;
      $apiParams['is_show_location'] = '1';
    }

    if ($parameters->doesParameterExists('is_active')) {
      $apiParams['is_active'] = $parameters->getParameter('is_active');
    }

    if ($parameters->doesParameterExists('is_public')) {
      $apiParams['is_public'] = $parameters->getParameter('is_public');
    }

    if ($parameters->doesParameterExists('organiser')) {
      $apiParams['created_id'] = $parameters->getParameter('organiser');
    }

    foreach($this->getParameterSpecification() as $spec) {
      if (stripos($spec->getName(), 'custom_')!==0) {
        continue;
      }
      if ($parameters->doesParameterExists($spec->getName())) {
        $apiParams[$spec->getApiFieldName()] = $parameters->getParameter($spec->getName());
      }
    }

    // Create the event through an API call.
    try {
      $results = civicrm_api4('Event', 'create', [ 'values' => $apiParams ]);
      $output->setParameter('id', $results[0]['id']);
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Exception\ExecutionException(E::ts('Could not create event from template.'));
    }
  }

}

?>
