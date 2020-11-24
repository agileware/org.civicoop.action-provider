<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
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

class ReplaceTokensInHTML extends AbstractAction {

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
    $contactData = array();
    $extraData = array();
    $contactId = $parameters->getParameter('contact_id');
    if ($parameters->doesParameterExists('participant_id')) {
      $extraData['participant']['id'] = $parameters->getParameter('participant_id');;
    }
    if ($parameters->doesParameterExists('case_id')) {
      $contactData['case_id'] = $parameters->getParameter('case_id');
    }
    if ($parameters->doesParameterExists('contribution_id')) {
      $contactData['contribution_id'] = $parameters->getParameter('contribution_id');
    }
    if ($parameters->doesParameterExists('activity_id')) {
      $contactData['activity_id'] = $parameters->getParameter('activity_id');
    }
    if ($extraData) {
      $contactData['extra_data'] = $extraData;
    }

    $html = '';
    if ($parameters->doesParameterExists('html')) {
      $html = $parameters->getParameter('html');
    } elseif ($this->configuration->doesParameterExists('html')) {
      $html = $this->configuration->getParameter('html');
    }

    $html = Tokens::replaceTokens($contactId, $html, $contactData);
    $output->setParameter('html', $html);
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
      new Specification('contact_id', 'Integer', E::ts('Contact ID'), true),
      new Specification('activity_id', 'Integer', E::ts('Activity ID'), false),
      new Specification('contribution_id', 'Integer', E::ts('Contribution ID'), false),
      new Specification('case_id', 'Integer', E::ts('Case ID'), false),
      new Specification('participant_id', 'Integer', E::ts('Participant ID'), false),
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
      $htmlParameter
    ));
  }

  /**
   * Returns the specification of the output parameters of this action.
   *
   * This function could be overriden by child classes.
   *
   * @return SpecificationBag
   */
  public function getOutputSpecification() {
    return new SpecificationBag([
      new Specification('html', 'String', E::ts('HTML Text'), true),
    ]);
  }

}
