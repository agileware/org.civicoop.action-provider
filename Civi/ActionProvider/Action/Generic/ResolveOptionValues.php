<?php
/**
 * @author Björn Endres <endres@systopia.de>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Action\Generic;

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBagInterface;
use \Civi\ActionProvider\Parameter\SpecificationBag;
use \Civi\ActionProvider\Parameter\Specification;
use \Civi\ActionProvider\Utils\CustomField;

use CRM_ActionProvider_ExtensionUtil as E;

/**
 * Class ResolveOptionValues
 *
 * A versatile generic action to resolve input data to option values
 * - single value or array
 * - match to value,label,name
 * - trim characters
 * - fuzzy matching
 *
 * @package Civi\ActionProvider\Action\Generic
 */
class ResolveOptionValues extends AbstractAction
{

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   */
  public function getConfigurationSpecification()
  {
    // option group list
    $optionGroups      = [];
    $option_groups_api = civicrm_api3('OptionGroup', 'get', ['options' => ['limit' => 0, 'is_active' => 1]]);
    foreach ($option_groups_api['values'] as $optionGroup) {
      $optionGroups[$optionGroup['id']] = $optionGroup['title'];
    }

    // match to options
    $match_on = [
        'value' => E::ts('Value'),
        'name'  => E::ts('Name'),
        'label' => E::ts('Label'),
    ];

    // matching options
    $match_type = [
        'best'  => E::ts('Best match'),
        'exact' => E::ts('Exact, discard others'),
        '0.9'   => E::ts('%1% match, discard others', [1 => 90]),
        '0.8'   => E::ts('%1% match, discard others', [1 => 80]),
        '0.7'   => E::ts('%1% match, discard others', [1 => 70]),
        '0.5'   => E::ts('%1% match, discard others', [1 => 50]),
    ];

    return new SpecificationBag(
        [
            new Specification('is_array', 'Boolean', E::ts('Multi-Value/List')),
            new Specification('option_group_id', 'String', E::ts('Option Group'), true, null, null, $optionGroups),
            new Specification('match_on', 'String', E::ts('Match On'), false, null, null, $match_on),
            new Specification('match_type', 'String', E::ts('Match Type'), true, null, null, $match_type),
            new Specification('trim_characters', 'String', E::ts('Trim Characters')),
        ]
    );
  }

  /**
   * Returns the specification of the configuration options for the actual action.
   *
   * @return SpecificationBag
   * @throws \Exception
   */
  public function getParameterSpecification()
  {
    return new SpecificationBag(
        [
            new Specification('input_value', 'String', E::ts('Input Value(s)'), true, null, null, null, true),
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
  public function getOutputSpecification()
  {
    return new SpecificationBag(
        [
            new Specification('output_value', 'String', E::ts('Output Value(s)'), true),
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
   * @return void
   * @throws \Exception
   */
  protected function doAction(ParameterBagInterface $parameters, ParameterBagInterface $output)
  {
    // get the input
    $input = $parameters->getParameter('input_value');

    // try to parse json
    if (!is_array($input)) {
      $decoded_input = json_decode($input, true);
      if (isset($decoded_input)) {
        $input = $decoded_input;
      }
    }

    // always work on arrays
    if (!is_array($input)) {
      $input = [$input];
    }

    // load option group (cached)
    $option_group_data = $this->loadOptionGroup(true);

    $mapped_result = [];
    foreach ($input as $value) {
      // trim the input value
      $value = $this->trim($value);

      // map/match/find the right option value
      $value = $this->map($value, $option_group_data);

      // if null, that means it wasn't found
      if (isset($value)) {
        $mapped_result[] = $value;
      }
    }

    // send result
    if (empty($this->configuration->getParameter('is_array'))) {
      // array mode is off, so convert back to single value
      $mapped_result = reset($mapped_result);
    }
    $output->setParameter('output_value', $mapped_result);
  }



  /**
   * Trim the value according to the configuration
   *
   * @param string $value
   *   input string
   *
   * @return string trimmed string
   */
  protected function map($value, $option_group_data)
  {
    $match_type = $this->configuration->getParameter('match_type');
    switch ($match_type) {
      case 'best':
        return $this->getMatchedValue($value, $option_group_data, 0.0);

      case 'exact':
        return $this->getMatchedValue($value, $option_group_data, 1.0);

      default:
        return $this->getMatchedValue($value, $option_group_data, $match_type);
    }
  }


  /**
   * Get the best matching value of the given option group
   *
   * @param string $value
   *   the value to be matched by a value from the option group
   *
   * @param array $option_group_data
   *   the option group data
   *
   * @param float $threshold
   *   threshold for match
   *
   * @param string $return_field
   *   the field of the option value to be returned (value, name, result)
   *
   * @return string|null
   *   the field value of the matched option value - if found.
   */
  protected function getMatchedValue($value, $option_group_data, $threshold, $return_field = 'value')
  {
    $field = $this->configuration->getParameter('match_on');
    $best_match = null;
    $best_match_score = 0.0;
    foreach ($option_group_data as $option_value) {
      if ($option_value[$field] == $value) {
        // this is a direct match, no need to look any further
        return $option_value[$return_field];

      } elseif ($threshold < 1.0) {
        // we have to do similarity search
        $score = 1.0 - (float) levenshtein($value, $option_value[$field]) / (float) max(strlen($value), strlen($option_value[$field]));
        if ($score >= $threshold && $score >= $best_match_score) {
          $best_match_score = $score;
          $best_match = $option_value;
        }
      }
    }

    if ($best_match) {
      return $best_match[$return_field];
    } else {
      return null;
    }
  }


  /**
   * Trim the value according to the configuration
   *
   * @param string $value
   *   input string
   *
   * @return string trimmed string
   */
  protected function trim($value)
  {
    if (is_string($value)) {
      $trim_chars = $this->configuration->getParameter('trim_characters');
      $value = trim($value, $trim_chars);
    }
    return $value;
  }

  /**
   * Retrieve the option group as an array of named arrays
   *
   * @param bool $cached
   *   should the result be cached (even between instances)
   *
   * @return array
   *   list of options as named array
   */
  protected function loadOptionGroup(bool $cached = true)
  {
    $option_group_id = (int) $this->configuration->getParameter('option_group_id');
    static $option_group_cache = [];

    // check for cache hits
    if ($cached && isset($option_group_cache[$option_group_id])) {
      return $option_group_cache[$option_group_id];
    }

    // load option group
    $result = \civicrm_api3('OptionValue', 'get', [
      'option_group_id' => $option_group_id,
      'return'          => 'name,label,value',
      'option.limit'    => 0,
    ]);

    // cache result
    $option_values = $result['values'];
    $option_group_cache[$option_group_id] = $option_values;
    return $option_values;
  }
}
