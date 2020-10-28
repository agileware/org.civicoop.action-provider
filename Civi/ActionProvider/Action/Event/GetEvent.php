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
use Civi\DataProcessor\DataSpecification\CustomFieldSpecification;
use CRM_ActionProvider_ExtensionUtil as E;

class GetEvent extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Event';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('event_id');
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
      new Specification('event_id', 'Integer', E::ts('Event ID'), false, null, null, null, FALSE),
    ));
    return $specs;
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    parent::setOutputFromEntity($entity, $output);
    try {
      if (!empty($entity['loc_block_id'])) {
        $loc = civicrm_api3('LocBlock', 'getsingle', ['id' => $entity['loc_block_id']]);
        $output->setParameter('address_id', $loc['address_id']);
      }
    } catch (\Exception $e) {
      // Do nothing
    }

  }


}
