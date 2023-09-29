<?php
/**
 * @author  Agileware <projects@agileware.com.au>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\PaymentToken;

use Civi\ActionProvider\Parameter\ParameterBag;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\Specification;
use Civi\Api4\PaymentToken;

class CreateOrUpdatePaymentToken extends AbstractAction {
	const specDefaults = [
		'data_type'     => 'String',
		'required'      => false,
		'default_value' => null,
		'fk_entity'     => null,
		'options'       => null,
		'multiple'      => false,
	];

	/**
	 * @inheritDoc
	 */
	protected function doAction( ParameterBagInterface $parameters, ParameterBagInterface $output ) {
		$id = $parameters->getParameter('id');

		if(!is_null($id) && $id > 0) {
			$action = PaymentToken::update(FALSE);
		}
		else {
			$action = PaymentToken::create(FALSE);
		}

		$action->setValues($parameters->toArray())
		       ->addValue(
				   'payment_processor_id',
			       $this->configuration->getParameter('payment_processor_id')
		       );

		if(!empty($this->configuration->getParameter('clear_meta'))) {
			if(!$action->getValue('expiry_date')) {
				$action->addValue('expiry_date', null);
			}
			if(!$action->getValue('masked_acount_number')){
				$action->addValue('masked_account_number', null);
			}
		}


		$result = $action->execute()->first();

		$output->fromArray($result, $this->getOutputSpecification());
		
		return $output;
	}

	protected function validateParameters( ParameterBagInterface $parameters ) {
		$expiryDate = $parameters->getParameter('expiry_date');
		$parsedDate = null;

		// Handle MM/YY expiry dates
		if(isset($expiryDate) && !is_numeric($expiryDate)) {
			$parsedDate = date_create_immutable_from_format('d/m/y', '01/' . $expiryDate);
		}
		if($parsedDate) {
			$parsedDate = $parsedDate->modify('00:00:00')->add(date_interval_create_from_date_string('1 month - 1 second'));
			$parameters->setParameter('expiry_date', $parsedDate->format('YmdHis'));
		}

		return parent::validateParameters( $parameters );
	}

	/**
	 * @inheritDoc
	 */
	public function getConfigurationSpecification() {
		return new SpecificationBag([
			new Specification('clear_meta', 'Boolean', E::ts('Clear Expiry Date and Masked Card Number'), true, 0),
			new Specification('payment_processor_id', 'Integer', E::ts('Payment Processor'), true, null, 'PaymentProcessor', null, FALSE),
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getParameterSpecification() {
		$fields = PaymentToken::getFields(FALSE)
		                      ->addWhere('name', '!=', 'payment_processor_id')
		                      ->setAction('create')
		                      ->execute()
			->getArrayCopy();
		$specs = array_map(
			function( $field ) {
				[
					'name'          => $name,
					'data_type'     => $dataType,
					'title'         => $title,
					'required'      => $required,
					'default_value' => $defaultValue,
					'fk_entity'     => $fkEntity,
					'options'       => $options,
					'multiple'      => $multiple,
				] = $field + self::specDefaults;

				return new Specification($name, $dataType, $title, $required, $defaultValue, $fkEntity, $options, $multiple);
			},
			$fields
		);
		return new SpecificationBag($specs);
	}

	public function getOutputSpecification() {
		$fields = PaymentToken::getFields(FALSE)
		                      ->setAction('get')
		                      ->execute()
		                      ->getArrayCopy();
		$specs = array_map(
			function($field) {
				[
					'name'          => $name,
					'data_type'     => $dataType,
					'title'         => $title,
					'required'      => $required,
					'default_value' => $defaultValue,
					'fk_entity'     => $fkEntity,
					'options'       => $options,
					'multiple'      => $multiple,
				] = $field + self::specDefaults;

				return new Specification($name, $dataType, $title, $required, $defaultValue, $fkEntity, $options, $multiple);
			},
			$fields
		);
		return new SpecificationBag($specs);
	}

}