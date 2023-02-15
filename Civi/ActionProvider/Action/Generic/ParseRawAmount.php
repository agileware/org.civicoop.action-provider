<?php
/**
 * @author Erik Hommel <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Exception\InvalidConfigurationException;
use Civi\ActionProvider\Exception\InvalidParameterException;
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
    $decimals->setDescription(E::ts("Does the amount have decimals?<p>Please not that Decimals and Divide by 100 can not <em>both</em> be answered with Yes.</p>"));
    $divideHundred = new Specification('divide', 'Boolean', E::ts("Divide by 100?"), TRUE, FALSE);
    $divideHundred->setDescription(E::ts("Some data providers multiply amounts by 100 to avoid issues with decimal digits.<br />Does this amount have to be divided by 100 because this is the case?<p>Please not that Decimals and Divide by 100 can not <em>both</em> be answered with Yes.</p>"));
    $decimalsDigit = new Specification('decimals_digit', 'Integer', E::ts("Digit for Decimals"), FALSE, 1, NULL, $this->getMonetaryDigits());
    $decimalsDigit->setDescription(E::ts("Use a . or a , to separate the decimals"));
    $thousandsDigit = new Specification('thousands_digit', 'Integer', E::ts("Digit for Thousands"), FALSE, 0, NULL, $this->getMonetaryDigits());
    $thousandsDigit->setDescription(E::ts("Use a . or a , to separate the thousands"));
    return new SpecificationBag([$decimals, $divideHundred, $decimalsDigit, $thousandsDigit]);
  }

  /**
   * Method to get the possible digits to separate decimals and thousands
   *
   * @return array
   */
  private function getMonetaryDigits(): array {
    return ["none", ",", "."];
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
    return new SpecificationBag([new Specification('parsed_amount', 'Money', E::ts('Parsed Amount')),]);
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
    $output->setParameter('parsed_amount',  round($fixedAmount,2, PHP_ROUND_HALF_DOWN));
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
    $digits = $this->getMonetaryDigits();
    if ($decimals) {
      $amountParts = explode($digits[$decimalsDigit], $rawAmount);
      if ($thousandsDigit) {
        $amountParts[0] = str_replace($digits[$thousandsDigit], "", $amountParts[0]);
      }
      $fixedAmount = (float) $amountParts[0] . "." . substr($amountParts[1],0,2);
    }
    else {
      if ($thousandsDigit) {
        $fixedAmount = (float) str_replace( $digits[$thousandsDigit], "", $rawAmount);
      }
      else {
        $fixedAmount = $rawAmount;
      }
      if ($divide) {
        $fixedAmount = $fixedAmount / 100;
      }
    }
    return $fixedAmount;
  }

  /**
   * Decimals and divide can not both be true
   *
   * @return bool
   * @throws InvalidConfigurationException
   */
  public function validateConfiguration() {
    // decimals and divide can not both be true
    $decimals = $this->configuration->getParameter('decimals');
    $divide = $this->configuration->getParameter('divide');
    if ($decimals && $divide) {
      throw new InvalidConfigurationException(E::ts("Decimals and divide by 100 can not both be true"));
    }
    return parent::validateConfiguration();
  }

  /**
   * Method to specify help text for the action
   * @return false|string|void
   */
  public function getHelpText() {
    E::ts("This action formats an incoming string which holds an amount. You can specify if the amount contains decimals or if the amount
    should be divided by 100 (sometimes amounts coming in from services have an amount mulitplied by 100 to avoid the decimal dot or comma issue),
    what character is used as a decimal delimiter and what is used a a thousands separator. The action will return an amount in a format that
    can be used for further processing in CiviCRM.");
  }

}
