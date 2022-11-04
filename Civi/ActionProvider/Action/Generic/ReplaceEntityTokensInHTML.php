<?php
/**
 * @author Klaas Eikelboom <klaas.eikelboom@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use Civi\ActionProvider\Parameter\WysiwygSpecification;
use Civi\ActionProvider\Utils\Tokens;
use CRM_ActionProvider_ExtensionUtil as E;

class ReplaceEntityTokensInHTML extends AbstractAction {

  /**
   * Run the action
   *
   * @param ParameterInterface $parameters
   *   The parameters to this action.
   * @param ParameterBagInterface $output
   * 	 The parameters this action can send back
   * @return void
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    $entityId = $parameters->getParameter('entity_id');
    $html = '';
    if ($parameters->doesParameterExists('html')) {
      $html = $parameters->getParameter('html');
    } elseif ($this->configuration->doesParameterExists('html')) {
      $html = html_entity_decode($this->configuration->getParameter('html'));
    }
    $messageTokens = \CRM_Utils_Token::getTokens($html);
    $entityName = strtolower($this->configuration->getParameter('entity'));
    $entity = civicrm_api3($entityName, 'getsingle', ['id' => $entityId]);
    $tokenHtml = Tokens::replaceEntityTokens($entityName, $entity, $html, $messageTokens);
    $output->setParameter('html', $tokenHtml);
  }

  /**
   * Returns the specification of the parameters of the actual action.
   *
   * @return SpecificationBag
   */
  public function getParameterSpecification() {
    $htmlParameter = new Specification('html', 'String', E::ts('HTML Text'), false);
    $htmlParameter->setDescription(E::ts('Either enter the text above or select an parameter below.'));
    return new SpecificationBag(array(
      $htmlParameter,
      new Specification('entity_id', 'Integer', E::ts('Entity ID'), true),
    ));
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $htmlParameter = new WysiwygSpecification('html', E::ts('HTML Text'), false);
    $htmlParameter->setDescription(E::ts('Either select a parameter with the HTML text or enther the HTML text above.'));
    return new SpecificationBag(array(
      new Specification('entity', 'String', E::ts('Entity'), true),
      $htmlParameter
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overridden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('html', 'String', E::ts('HTML Text'), true),
    ]);
  }

}
