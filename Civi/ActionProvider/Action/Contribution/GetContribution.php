<?php

namespace Civi\ActionProvider\Action\Contribution;

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

class GetContribution extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Contribution';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('contribution_id');
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
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), false, null, null, null, FALSE),
    ));
    return $specs;
  }


}
