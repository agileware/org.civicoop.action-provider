<?php

namespace Civi\ActionProvider\Action\Generic;

use Civi\ActionProvider\Action\AbstractAction;
use Civi\ActionProvider\Parameter\ParameterBagInterface;
use Civi\ActionProvider\Parameter\SpecificationBag;
use Civi\ActionProvider\Parameter\Specification;

use CRM_ActionProvider_ExtensionUtil as E;

class LogParameterValues extends AbstractAction {

  protected const PARAMETERS = 10;
  protected const PREVIEW_PARAMETERS = 10;
  protected const PREVIEW_LIMIT = 200;

  protected const PREFIX_LABEL = 'label_';
  protected const PREFIX_VALUE = 'value_';

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification() {
    $active = new Specification('is_active', 'Boolean', E::ts('Is active'), TRUE, TRUE);
    $title = new Specification('title', 'String', E::ts('Title'), FALSE, '');
    $spec = [
      $active,
      $title,
    ];
    for ($i = 1; $i <= self::PARAMETERS; $i++) {
      $spec[] = new Specification(self::PREFIX_LABEL . $i, 'String', E::ts('Label') . ' ' . $i, FALSE, '');
    }

    return new SpecificationBag($spec);
  }

  /**
   * Returns the specification of the configuration options for the actual
   * action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification() {
    $spec = [];
    for ($i = 1; $i <= self::PARAMETERS; $i++) {
      // we must use multiple here to support all kinds of parameter types
      $spec[] = new Specification(self::PREFIX_VALUE . $i, 'String', E::ts('Value') . ' ' . $i, FALSE, '', NULL, [], TRUE);
    }
    return new SpecificationBag($spec);
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
      new Specification('debug_message', 'String', E::ts('Debug Message')),
    ]);
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
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output) {
    if (!$this->configuration->getParameter('is_active')) {
      return;
    }

    $title = (string) $this->configuration->getParameter('title');

    $params = [];
    $preview_parts = [];
    for ($i = 1; $i <= self::PARAMETERS; $i++) {
      $param_name = self::PREFIX_VALUE . $i;
      if (!$parameters->parameterExists($param_name)) {
        continue;
      }

      // unify parameters (just to be sure...)
      $raw = $parameters->getParameter($param_name);
      if ($raw === NULL) {
        continue;
      }
      if (is_array($raw)) {
        $value = $raw;
      }
      else {
        $value = [$raw];
      }

      $label = $this->configuration->getParameter(self::PREFIX_LABEL . $i);
      if (isset($label) && $label !== '') {
        $label_text = $label . ':';
      }
      else {
        $label_text = $i . ':';
      }
      if ($i <= self::PREVIEW_PARAMETERS) {
        $preview_parts[] = '[' . $label_text . implode(',', $value) . ']';
      }

      // flatten for shorter output
      if (count($value) === 1) {
        $value = reset($value);
      }

      $index = $i;
      if (!empty($label)) {
        $index .= ' ' . $label;
      }
      $params[$index] = $value;
    }
    $full_preview = implode(', ', $preview_parts);
    $output->setParameter('debug_message', $title . ' - ' . $full_preview);
    $preview = mb_substr($full_preview, 0, self::PREVIEW_LIMIT) . ' ...';
    \Civi::log()->debug('Action Provider: LogParameterValues ' . $title . ' - ' . $preview, $params);
  }

}
