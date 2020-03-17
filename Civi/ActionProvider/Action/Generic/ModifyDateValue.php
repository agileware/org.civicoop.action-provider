<?php
/**
 * @author Jens Schuppe <schuppe@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class ModifyDateValue extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $format = new Specification(
      'format',
      'String',
      E::ts('Format'),
      false,
      'Ymd',
      '',
      null,
      false
    );
    $format->setDescription("The format is the input format of the date. The following modifiers are available:
      <ul>
        <li>Y: year four digit (e.g. 2018)</li>
        <li>m: month two digit (eg 03)</li>
        <li>d: day two digit (eg 04)</li>
        <li>H: hours in 24 format with a preceding 0 (eg 08)</li>
        <li>i: minute with a preceding 0 (eg 06)</li>
      </ul>
      <a href=\"http://nl1.php.net/manual/en/datetime.createfromformat.php#refsect1-datetime.createfromformat-parameters\">More information at php.net</a><br />
      The default value is <code>Ymd</code>, as returned by Date input parameters.");

    $date = new Specification(
      'date_interval',
      'String',
      E::ts('Date interval'),
      true
    );
    $date->setDescription('Set the date interval. Use date formats from <a href="https://www.php.net/manual/de/datetime.formats.php">PHP.net</a>.');

    return new SpecificationBag(array(
      $format,
      $date,
      new Specification(
        'include_time',
        'Boolean',
        E::ts('Include time'),
        TRUE,
        '0',
        '',
        array(
          '0' => E::ts('No'),
          '1' => E::ts('Yes'),
        )
      ),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(array(
      new Specification(
        'date',
        'String',
        E::ts('Date'),
        true
      )
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(array(
      new Specification(
        'date',
        'Date',
        E::ts('Date')
      ),
    ));
  }

  /**
   * Validates parameters.
   *
   * @param \Civi\ActionProvider\Parameter\ParameterBagInterface $parameters
   *
   * @return bool
   */
  public function validateParameters(ParameterBagInterface $parameters) {
    if (!$date = \DateTime::createFromFormat(
      $this->configuration->getParameter('format'),
      $parameters->getParameter('date')
    )) {
      return false;
    }

    if (!$date->modify(
      $this->configuration->getParameter('date_interval')
    )) {
      return false;
    }

    return parent::validateParameters($parameters);
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $date = \DateTime::createFromFormat(
      $this->configuration->getParameter('format'),
      $parameters->getParameter('date')
    );

    $date->modify($this->configuration->getParameter('date_interval'));

    if (
      $this->configuration->doesParameterExists('include_time')
      && $this->configuration->getParameter('include_time')
    ) {
      $format = 'YmdHis';
    }
    else {
      $format = 'Ymd';
    }

    $output->setParameter('date', $date->format($format));
  }

}
