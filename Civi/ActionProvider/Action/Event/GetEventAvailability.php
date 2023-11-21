<?php

namespace Civi\ActionProvider\Action\Event;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Action\AbstractGetSingleAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Utils\CustomField;

use Civi\ActionProvider\Utils\Fields;
use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Event;
use Civi\Api4\Participant;
use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use CRM_ActionProvider_ExtensionUtil as E;
use CRM_Core_Exception;

class GetEventAvailability extends AbstractAction {


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
      new Specification('event_id', 'Integer', E::ts('Event ID'), false, null, null, null, FALSE),
    ));
    return $specs;
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
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    try {
      $event = Event::get(FALSE)
        ->addWhere('id', '=', $parameters->getParameter('event_id'))
        ->execute()
        ->first();
      if (isset($event['max_participants'])) {
        $output->setParameter('max_participants', $event['max_participants']);
        $participantCount = Participant::get(FALSE)
          ->addWhere('event_id', '=', $parameters->getParameter('event_id'))
          ->addWhere('status_id.is_counted', '=', TRUE)
          ->execute()
          ->count();
        $availablePlaces = $event['max_participants'] - $participantCount;
        $output->setParameter('available_places', $availablePlaces);
        if (!$availablePlaces) {
          if (!empty($event['has_waitlist']) && !empty($event['waitlist_text'])) {
            $output->setParameter('full_text', $event['waitlist_text']);
          } else {
            $output->setParameter('full_text', $event['event_full_text']);
          }
        }
      }
    }
    catch (UnauthorizedException|CRM_Core_Exception $e) {
    }
  }

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
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('max_participants', 'Integer', E::ts('Max participants'), false),
      new Specification('available_places', 'Integer', E::ts('Available Places'), false),
      new Specification('full_text', 'String', E::ts('Text when event is full'), false)
    ]);
  }

}
