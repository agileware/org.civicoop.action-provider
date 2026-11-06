<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Contribution;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\Specification;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\Api4\ContributionRecur;

/**
 * Create or update a recurring contribution with any ContributionRecur field.
 *
 * Unlike CreateContributionRecur this exposes every field (trxn_id, invoice_id,
 * payment_instrument_id, is_test, ...) and outputs the full saved record, which
 * is what a payment processor integration needs to store a tokenised recurring
 * payment. Configuration values act as defaults; a mapped parameter of the same
 * name takes precedence.
 */
class CreateOrUpdateContributionRecur extends AbstractAction {

  /**
   * Fields only set through configuration, never mapped as parameters.
   */
  const CONFIGURATION_ONLY_FIELDS = [
    'payment_processor_id',
    'payment_instrument_id',
    'is_test',
    'auto_renew',
  ];

  /**
   * Fields that can be configured, and overridden by a mapped parameter.
   */
  const CONFIGURATION_FIELDS = [
    'payment_processor_id',
    'payment_instrument_id',
    'is_test',
    'financial_type_id',
    'frequency_unit',
    'frequency_interval',
    'installments',
    'currency',
    'auto_renew',
    'contribution_status_id',
  ];

  const SPEC_DEFAULTS = [
    'data_type' => 'String',
    'title' => NULL,
    'required' => FALSE,
    'default_value' => NULL,
    'fk_entity' => NULL,
    'options' => NULL,
    'multiple' => FALSE,
    'description' => NULL,
  ];

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \CRM_Core_Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $id = $parameters->getParameter('id');

    if (!empty($id)) {
      $action = ContributionRecur::update(FALSE);
    }
    else {
      $action = ContributionRecur::create(FALSE);
    }

    // Empty configuration values are left out so they don't blank a field;
    // "0" is kept because it is a real value for is_test and auto_renew.
    $configuration = array_filter(
      $this->configuration->toArray(),
      fn($value) => $value !== NULL && $value !== ''
    );
    $values = array_merge($configuration, $parameters->toArray());
    if (empty($id)) {
      unset($values['id']);
    }

    $result = $action->setValues($values)->execute()->first();

    $output->fromArray($result, $this->getOutputSpecification());
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $fields = ContributionRecur::getFields(FALSE)
      ->addWhere('name', 'IN', self::CONFIGURATION_FIELDS)
      ->setAction('create')
      ->setLoadOptions(TRUE)
      ->execute()
      ->getArrayCopy();

    return new SpecificationBag(array_map([self::class, 'mapToSpecification'], $fields));
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $fields = ContributionRecur::getFields(FALSE)
      ->addWhere('name', 'NOT IN', self::CONFIGURATION_ONLY_FIELDS)
      ->setAction('create')
      ->setLoadOptions(TRUE)
      ->execute()
      ->getArrayCopy();

    return new SpecificationBag(array_map([self::class, 'mapToSpecification'], $fields));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    $fields = ContributionRecur::getFields(FALSE)
      ->setAction('get')
      ->setLoadOptions(TRUE)
      ->execute()
      ->getArrayCopy();

    return new SpecificationBag(array_map([self::class, 'mapToSpecification'], $fields));
  }

  /**
   * Build an action provider specification from an APIv4 field definition.
   *
   * @param array $field
   *
   * @return Specification
   */
  protected static function mapToSpecification(array $field) {
    $field += self::SPEC_DEFAULTS;

    // Foreign keys are picked by id, and getFields returns FALSE or an empty
    // array for fields without options.
    $options = $field['options'];
    if (!empty($field['fk_entity']) || empty($options) || !is_array($options)) {
      $options = NULL;
    }

    return new Specification(
      $field['name'],
      $field['data_type'],
      $field['title'] ?? $field['name'],
      (bool) $field['required'],
      $field['default_value'],
      $field['fk_entity'],
      $options,
      (bool) $field['multiple'],
      $field['description'] ?? ''
    );
  }

}
