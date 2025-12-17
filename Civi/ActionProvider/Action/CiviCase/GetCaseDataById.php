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
use Civi\ActionProvider\Utils\Fields;
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

  public function getOutputSpecification() {
    $bag = new SpecificationBag();
    Fields::getFieldsForEntity($bag, $this->getApiEntity(), 'get', $this->getSkippedFields(), $this->getEntityAlias());
    $bag->addSpecification(new Specification('type_name', 'String', E::ts('Case Type Name'), false));
    $bag->addSpecification(new Specification('type_title', 'String', E::ts('Case Type Title'), false));
    return $bag;
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

  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    try {
      $id = $this->getIdFromParamaters($parameters);
      $entity = civicrm_api3($this->getApiEntity(),
        'getsingle',
        array(
          'id' => $id
        )
      );
      if ($entity) {
        $caseType = \Civi\Api4\CaseType::get(FALSE)
          ->addSelect('name', 'title')
          ->addWhere('id', '=', (int) $entity['case_type_id'])
          ->execute()->single();

        if (!empty($caseType)) {
          $entity['type_name'] = $caseType['name'];
          $entity['type_title'] = $caseType['title'];
        }

        $this->setOutputFromEntity($entity, $output);
      }
    }
    catch (\Exception $e) {
      // Do nothing
    }
  }

  protected function setOutputFromEntity($entity, ParameterBagInterface $output) {
    parent::setOutputFromEntity($entity, $output);
    $output->setParameter('contact_id', reset($entity['contact_id']));
    $output->setParameter('case_type_id', $entity['case_type_id']);
  }


}
