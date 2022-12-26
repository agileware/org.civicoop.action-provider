<?php
/**
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use Civi\FormProcessor\API\Exception;
use CRM_ActionProvider_ExtensionUtil as E;

class ParseRawAmount extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $decimals = new Specification('decimals', 'Boolean', E::ts("Decimals?"), TRUE, FALSE);
    $decimals->setDescription(E::ts("Does the amount have decimals?"));
    $divideHundred = new Specification('divide', 'Boolean', E::ts("Divide by 100?"), TRUE, FALSE);
    $decimals->setDescription(E::ts("Some data providers multiply amounts by 100 to avoid issues with decimal digits. Does this amount have to be divided by 100 because this is the case?"));
    $decimalsDigit = new Specification('decimals_digit', 'Integer', E::ts("Digit for Decimals"), TRUE, 1, NULL, $this->getMonetaryDigits());
    $decimalsDigit->setDescription(E::ts("Use a . or a , to separate the decimals"));
    $thousandsDigit = new Specification('thousands_digit', 'Integer', E::ts("Digit for Thousands"), TRUE, 2, NULL, $this->getMonetaryDigits());
    $thousandsDigit->setDescription(E::ts("Use a . or a , to separate the thousands"));
    return new SpecificationBag([$decimals, $divideHundred, $decimalsDigit, $thousandsDigit]);
  }

  /**
   * Method to get the possible digits to separate decimals and thousands
   *
   * @return array
   */
  private function getMonetaryDigits(): array {
    return [
      1 => ",",
      2 => ".",
    ];
  }

  /**
   * Returns the specification of the parameter options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification(): SpecificationBag {
    $rawAmount = new Specification('raw_amount', 'String', E::ts('Raw Amount'));
    $rawAmount->setDescription(E::ts("This is a string containing a raw date amount, potentially including decimals and dots or comma's for decimals or thousands."));
    return new SpecificationBag([$rawAmount]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification(): specificationBag {
    return new SpecificationBag([new Specification('parsed_amount', 'Float', E::ts('Parsed Amount')),]);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $rawAmount = $parameters->getParameter('raw_amount');
    $decimals = $this->configuration->getParameter('decimals');
    $divide = $this->configuration->getParameter('divide');
    $decimalsDigit = $this->configuration->getParameter('decimals_digit');
    $thousandsDigit = $this->configuration->getParameter('thousands_digit');
    $fixedAmount = $this->formatAmount($rawAmount, $decimalsDigit, $thousandsDigit, $decimals, $divide);
    $output->setParameter('parsed_amount',  $fixedAmount);
  }

  /**
   * Method to format the incoming amount
   *
   * @param string $rawAmount
   * @param int $decimalsDigit
   * @param int $thousandsDigit
   * @param bool $decimals
   * @param bool $divide
   * @return float
   */
  private function formatAmount(string $rawAmount, int $decimalsDigit, int $thousandsDigit, bool $decimals, bool $divide): float {
    $fixedAmount = 0.0;
    if ($decimals) {
      $amountParts = explode($decimalsDigit, $rawAmount);
      $amountParts[0] = str_replace($thousandsDigit, "", $amountParts[0]);
      $fixedAmount = (float) $amountParts[0] . \Civi::settings()->get('monetaryDecimalPoint'). $amountParts[1];
    }
    else {
      $fixedAmount = (float) str_replace($thousandsDigit, "", $rawAmount);
      if ($divide) {
        $fixedAmount = $fixedAmount / 100;
      }
    }
    return round($fixedAmount, 2, PHP_ROUND_HALF_UP);
  }

}
