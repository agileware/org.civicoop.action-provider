<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\CiviCase;

use Civi\ActionProvider\Action\AbstractGetSingleAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use CRM_ActionProvider_ExtensionUtil as E;

class GetCaseDataById extends AbstractGetSingleAction {

  /**
   * Returns the name of the entity.
   *
   * @return string
   */
  protected function getApiEntity() {
    return 'Case';
  }

  /**
   * Returns the ID from the parameter array
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return int
   */
  protected function getIdFromParamaters(ParameterBagInterface $parameters) {
    return $parameters->getParameter('case_id');
  }

  /**
   * @return array
   */
  protected function getSkippedFields() {
    return ['contacts', 'activities', 'client_id'];
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('case_id', 'Integer', E::ts('Case ID'), true),
    ]);
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    parent::setOutputFromEntity($entity, $output);
    $output->setParameter('contact_id', reset($entity['contact_id']));
    $output->setParameter('case_type_id', $entity['case_type_id']);
  }


}
