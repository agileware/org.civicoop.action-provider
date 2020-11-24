<?php

namespace Civi\ActionProvider\Parameter;

use Civi\ActionProvider\Exception\InvalidParameterException;
use CRM_ActionProvider_ExtensionUtil as E;

class WysiwygSpecification extends Specification {

  public $enableCaseTokens = true;

  public $enableContributionTokens = true;

  public $enableActivityTokens = true;

  public $enableParticipantTokens = true;

  /**
   * @param string $name
   * @param string $title
   * @param bool $required
   */
  public function __construct($name, $title='', $required = false) {
    $this->setName($name);
    $this->setDataType('String');
    $this->setTitle($title);
    $this->setRequired($required);
    $this->options = null;
  }

  /**
   * @return bool
   */
  public function getWysiwyg() {
    return true;
  }

  public function getAvailableTokens() {
    //get the tokens.
    $tokens = \CRM_Core_SelectValues::contactTokens();
    $tokens = array_merge($tokens, \CRM_Core_SelectValues::domainTokens());
    if ($this->enableContributionTokens) {
      $tokens = array_merge($tokens, \CRM_Core_SelectValues::contributionTokens());
    }
    if ($this->enableActivityTokens) {
      $tokens = array_merge($tokens, \CRM_Core_SelectValues::activityTokens());
    }
    if ($this->enableParticipantTokens) {
      $tokens = array_merge($tokens, \CRM_Core_SelectValues::participantTokens());
    }
    if ($this->enableCaseTokens) {
      $tokens = array_merge($tokens, \CRM_Core_SelectValues::caseTokens());
    }
    return \CRM_Utils_Token::formatTokensForDisplay($tokens);
  }

}
