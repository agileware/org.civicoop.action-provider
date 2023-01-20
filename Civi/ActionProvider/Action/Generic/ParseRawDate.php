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

class ParseRawDate extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification(): SpecificationBag {
    $separator = new Specification('separator', 'Integer', E::ts("Separator"), TRUE, 0, NULL, $this->getSeparatorValues());
    $separator->setDescription(E::ts("Select the character used to separate the different elements of the date"));
    $yearPart = new Specification('year_part', 'Integer', E::ts("Year Part"), TRUE, 2, NULL, [1,2,3]);
    $yearPart->setDescription(E::ts("Select if the year is in the first (1), middle (2) of last (3) part of the date"));
    $monthPart = new Specification('month_part', 'Integer', E::ts("Month Part"), TRUE, 1, NULL, [1,2,3]);
    $monthPart->setDescription(E::ts("Select if the month is in the first (1), middle (2) of last (3) part of the date"));
    $dayPart = new Specification('day_part', 'Integer', E::ts("Day Part"), TRUE, 0, NULL, [1,2,3]);
    $dayPart->setDescription(E::ts("Select if the day is in the first (1), middle (2) of last (3) part of the date"));
    $cutOff = new Specification('cutoff', 'Integer', E::ts("Cut off for millenium"), FALSE, 0, NULL, $this->getCutOffValues());
    $cutOff->setDescription(E::ts("Cut off point (2 numbers) for millenium if year only has 2 digits.
        <p> So if the cut of is 40, a year of 35 becomes 2035 and a year of 40 becomes 1940</p>"));
    return new SpecificationBag([$separator, $yearPart, $monthPart, $dayPart, $cutOff]);
  }

  /**
   * Method to get the cut off values
   *
   * @return array
   */
  private function getCutOffValues(): array {
    $cutOff = 1;
    $cutOffValues = [];
    while ($cutOff <= 99) {
      if ($cutOff < 10) {
        $cutOffValues[] = str_pad($cutOff, 2, "0", STR_PAD_LEFT);
      }
      else {
        $cutOffValues[] = (string) $cutOff;
      }
      $cutOff++;
    }
    return $cutOffValues;
  }

  /**
   * Method to get a list of date separator values
   *
   * @return string[]
   */
  private function getSeparatorValues(): array {
    return ["-", "/", ".", ",", ";", ":"];
  }

  /**
   * Returns the specification of the parameter options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification(): SpecificationBag {
    $rawDate = new Specification('raw_date', 'String', E::ts('Raw Date'));
    $rawDate->setDescription(E::ts("This is a string containing a raw date without time, for example 5/3/2022 or 14-06-21. This action can be used to transform a date that can not be parsed with the standard PHP functions into a valid date in the default CiviCRM format Ymd."));
    return new SpecificationBag([$rawDate]);
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification(): specificationBag {
    return new SpecificationBag([new Specification('parsed_date', 'Date', E::ts('Parsed Date (Ymd)')),]);
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
    $rawDate = $parameters->getParameter('raw_date');
    $separator = (int) $this->configuration->getParameter('separator');
    $yearPart = (int) $this->configuration->getParameter('year_part');
    $monthPart = (int) $this->configuration->getParameter('month_part');
    $dayPart = (int) $this->configuration->getParameter('day_part');
    $cutOff = (int) $this->configuration->getParameter('cutoff');
    $dateParts = $this->explodeRawDate($rawDate, $separator);
    $fixedDate = new \DateTime();
    $year = $this->getYear($dateParts, $yearPart, $cutOff);
    $fixedDate->setDate($year, $dateParts[$monthPart], $dateParts[$dayPart]);
    $output->setParameter('parsed_date',  $fixedDate->format('Ymd'));
  }

  /**
   * Method to deal with year having 2 or 4 digits
   *
   * @param array $dateParts
   * @param int $yearPart
   * @param int|NULL $cutOff
   * @return int
   */
  private function getYear(array $dateParts, int $yearPart, ?int $cutOff=NULL): int {
    $year = $dateParts[$yearPart];
    if (strlen($year) == 2) {
      if ($cutOff) {
        $cutOffValues = $this->getCutOffValues();
        $cutOffYear = $cutOffValues[$cutOff];
        if ($cutOffYear && $year < $cutOffYear) {
          $year = "20" . $year;
        }
        else {
          $year = "19" . $year;
        }
      }
    }
    return (int) $year;
  }

  /**
   * Method to explode the raw date into date parts
   *
   * @param string $rawDate
   * @param int $separator
   * @return array
   */
  private function explodeRawDate(string $rawDate, int $separator): array {
    $separators = $this->getSeparatorValues();
    return explode($separators[$separator], $rawDate);
  }
  /**
   * Method to specify help text for the action
   * @return false|string|void
   */
  public function getHelpText() {
    E::ts("This action formats an incoming string which holds a date. You can specify what separator is used for the date
    and what the year, month and day parts are. You can also select where the cutoff will be for the millennium when the year is 2 digits only.
    Anything before the cutoff will be 20xx and anything after will be 19xx. The action will return a date in a format that can be used for further processing in CiviCRM.");
  }

}
