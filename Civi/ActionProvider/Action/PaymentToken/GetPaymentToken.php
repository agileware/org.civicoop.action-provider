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

use CRM_ActionProvider_ExtensionUtil as E;

class GetPaymentToken extends AbstractAction {

	/**
	 * @inheritDoc
	 */
	protected function doAction( ParameterBagInterface $parameters, ParameterBagInterface $output ) {
		$action = PaymentToken::get(FALSE);

		$specs = $this->getParameterSpecification();

		foreach($parameters as $key => $parameter) {
			$spec = $specs->getSpecificationByName($key);
			if($spec->isMultiple()) {
				$action->addWhere( $key, 'IN', $parameters );
			}
			else {
				$action->addWhere( $key, '=', $parameter );
			}
		}

		$action->addWhere( 'payment_processor_id', '=', $this->configuration->getParameter('payment_processor_id') );

		$result = $action->execute()->first();

		$output->fromArray($result, $this->getOutputSpecification());

		return $output;
	}

	protected function validateParameters( ParameterBagInterface $parameters ) {
		$expiryDate = $parameters->getParameter('expiry_date');

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
			new Specification('payment_processor_id', 'Integer', E::ts('Payment Processor'), true, null, 'PaymentProcessor', null, FALSE),
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getParameterSpecification() {
		$keys = [ 'name', 'data_type', 'title', 'required', 'default_value', 'fk_entity', 'options', 'multiple' ];
		$fields = PaymentToken::getFields(FALSE)
			->addWhere('name', '!=', 'payment_processor_id')
			->setAction('get')
			->execute()
			->getArrayCopy();
		$specs = array_map(
			function($field) use($keys) {
				[ $name, $dataType, $title, $required, $defaultValue, $fkEntity, $options, $multiple ] = array_map(fn($k) => $field[$k], $keys);

				return new Specification($name, $dataType, $title, ($name == 'contact_id'), $defaultValue, $fkEntity, $options, $multiple);
			},
			$fields
		);
		return new SpecificationBag($specs);
	}

	public function getOutputSpecification() {
		$keys = [ 'name', 'data_type', 'title', 'required', 'default_value', 'fk_entity', 'options', 'multiple' ];
		$fields = PaymentToken::getFields(FALSE)
		                      ->setAction('get')
		                      ->execute()
		                      ->getArrayCopy();
		$specs = array_map(
			function($field) use($keys) {
				[ $name, $dataType, $title, $required, $defaultValue, $fkEntity, $options, $multiple ] = array_map(fn($k) => $field[$k], $keys);

				return new Specification($name, $dataType, $title, $required, $defaultValue, $fkEntity, $options, $multiple);
			},
			$fields
		);
		return new SpecificationBag($specs);
	}

}