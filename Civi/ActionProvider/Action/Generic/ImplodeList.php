<?php
/**
 * @author Klaas Eikelboom <klaas.eikelboom@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class ImplodeList extends AbstractAction {

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    return new SpecificationBag(
      [
        new Specification(
          'separator',
          'String',
          E::ts('Separator'),
          TRUE
        ),
      ]
    );
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    return new SpecificationBag(
      [
        new Specification(
          'value',
          'String',
          E::ts('Value'),
          TRUE,
          NULL,
          NULL,
          NULL,
          TRUE
        ),
      ]
    );
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag(
      [
        new Specification(
          'value',
          'String',
          E::ts('Value'),
          FALSE,
          NULL,
          NULL,
          NULL,
          TRUE
        ),
      ]
    );
  }

  /**
   * Run the action
   *
   * @param ParameterBagInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   *   The parameters this action can send back
   *
   * @return void
   * @throws \Exception
   */
  protected function doAction(
    ParameterBagInterface $parameters,
    ParameterBagInterface $output
  ) {
    $output->setParameter(
      'value',
      implode(
        $this->configuration->getParameter('separator'),
        $parameters->getParameter('value')
      )
    );
  }

}
