<?php

namespace Civi\ActionProvider\Action\CiviCase;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\OptionGroupSpecification;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class CreateCase extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
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
    return new SpecificationBag(
      [
        new Specification('case_type_id', 'Integer', E::ts('Default Case Type'), TRUE, NULL, 'CaseType', NULL, FALSE),
        new Specification('subject', 'String', E::ts('Default Subject'), TRUE, NULL, NULL, NULL, FALSE),
        new Specification('creator_id', 'Integer', E::ts('Creator Contact ID (Case Manager)'), FALSE, NULL, 'Contact', NULL, FALSE),
        new OptionGroupSpecification('case_status', 'case_status', E::ts('Case Status')),
      ]
    );
  }

  /**
   * Returns the specification of the configuration options for the action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    return new SpecificationBag([
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), TRUE, NULL, NULL, NULL, FALSE),
      new Specification('creator_id', 'Integer', E::ts('Creator Contact ID (Case Manager)'), FALSE, NULL, 'Contact', NULL, FALSE),
      new OptionGroupSpecification('case_status', 'case_status', E::ts('Case Status')),
      new Specification('subject', 'String', E::ts('Subject'), false),
      new Specification('created_date', 'Date', E::ts('Created date'), false),
      new Specification('start_date', 'Date', E::ts('Start date'), false),
      new Specification('end_date', 'Date', E::ts('End date'), false),
      new Specification('case_type_id', 'Integer', E::ts('Case Type'), false, NULL, 'CaseType', NULL, FALSE),
    ]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(
      [new Specification('case_id', 'Integer', E::ts('Case ID'), FALSE)]
    );
  }

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    // Get the contact.
    $contact_id = $parameters->getParameter('contact_id');

    // Get the case type and subject.
    $createParams['case_type_id'] = $this->configuration->getParameter('case_type_id');
    $createParams['subject'] = $this->configuration->getParameter('subject');
    $createParams['contact_id'] = $contact_id;
    if ($parameters->getParameter('creator_id')) {
      $createParams['creator_id'] = $parameters->getParameter('creator_id');
    }
    elseif ($this->configuration->getParameter('creator_id')) {
      $createParams['creator_id'] = $this->configuration->getParameter('creator_id');
    }
    if ($parameters->getParameter('case_status')) {
      $createParams['status_id'] = $parameters->getParameter('case_status');
    }
    elseif ($this->configuration->getParameter('case_status')) {
      $createParams['status_id'] = $this->configuration->getParameter('case_status');
    }
    if ($parameters->getParameter('subject')) {
      $createParams['subject'] = $parameters->getParameter('subject');
    }
    elseif ($this->configuration->getParameter('subject')) {
      $createParams['subject'] = $this->configuration->getParameter('subject');
    }
    if ($parameters->doesParameterExists('created_date')) {
      $createParams['created_date'] = $parameters->getParameter('created_date');
    }
    if ($parameters->doesParameterExists('start_date')) {
      $createParams['start_date'] = $parameters->getParameter('start_date');
    }
    if ($parameters->doesParameterExists('end_date')) {
      $createParams['end_date'] = $parameters->getParameter('end_date');
    }
    if ($parameters->getParameter('case_type_id')) {
      $createParams['case_type_id'] = $parameters->getParameter('case_type_id');
    }

    // Create the case through an API call.
    try {
      $result = civicrm_api3('Case', 'create', $createParams);
    } catch (Exception $e) {
      throw new \Civi\ActionProvider\Action\Exception\ExecutionException(E::ts('Could not create case'));
    }
    $case_id = \CRM_Utils_Array::value('id', $result);
    $output->setParameter('case_id', $case_id);
  }
}
