<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Parameter;

use Civi\ActionProvider\Exception\InvalidParameterException;

class SpecificationCollection implements SpecificationInterface {

  /**
   * @var SpecificationBag
   */
  protected $specificationBag;

  /**
   * @var false|int
   */
  protected $min = false;

  /**
   * @var false|int
   */
  protected $max = false;

  /**
   * @var string
   */
  protected $name;
  /**
   * @var string
   */
  protected $title;
  /**
   * @var string
   */
  protected $description;

  /**
   * @param $name
   * @param $dataType
   */
  public function __construct($name, $title,  SpecificationBag $specificationBag, $min=false, $max=false, $description='') {
    $this->setName($name);
    $this->setTitle($title);
    $this->setMin($min);
    $this->setMax($max);
    $this->specificationBag = $specificationBag;
    $this->setDescription($description);
  }

  /**
   * @return \Civi\ActionProvider\Parameter\SpecificationBag
   */
  public function getSpecificationBag() {
    return $this->specificationBag;
  }

  /**
   * @return String
   */
  public function getType() {
    return 'collection';
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param string $title
   *
   * @return $this
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * Returns the minimum count for the collection. False if not set.
   *
   * @return false|int
   */
  public function getMin() {
    return $this->min;
  }

  /**
   * Sets the minimum count for the collection. False if not set.
   *
   * @param false|int
   * @return SpecificationCollection
   */
  public function setMin($min) {
    $this->min = $min;
    return $this;
  }

  /**
   * Returns the maximum count for the collection. False if not set.
   *
   * @return false|int
   */
  public function getMax() {
    return $this->max;
  }

  /**
   * Sets the maximum count for the collection. False if not set.
   *
   * @param false|int
   * @return SpecificationCollection
   */
  public function setMax($max) {
    $this->max = $max;
    return $this;
  }

  /**
   * @return array
   */
  public function toArray() {
    $ret = array();
    foreach (get_class_methods($this) as $method) {
      $var = false;
      if (stripos($method, 'get') === 0) {
        $var = lcfirst(substr($method, 3));
      } elseif (stripos($method, 'is') === 0) {
        $var = lcfirst(substr($method, 2));
      }
      if (!$var) {
        continue;
      }

      $key = strtolower(preg_replace('/(?=[A-Z])/', '_$0', $var));
      $ret[$key] = $this->$method();
    }
    $ret['specification_bag'] = $this->specificationBag->toArray();
    return $ret;
  }

  /**
   * Validate a value
   *
   * @param $collection
   * @return bool
   * @throws \CRM_Core_Exception
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  public function validate($collection) {
    if (!is_array($collection)) {
      return false;
    }
    $count = count($collection);
    if ($this->min !== false && $count < $this->min) {
      throw new InvalidParameterException($this->getName() . " invalid. Provide at least ".$this->min);
    }
    if ($this->max !== false && $count > $this->max) {
      throw new InvalidParameterException($this->getName() . " invalid. Provide at most ".$this->max);
    }
    foreach($collection as $value) {
      if ($value instanceof ParameterBag) {
        $parameters = $value;
      } elseif (is_array($value)) {
        $parameters = new ParameterBag();
        foreach($value as $k => $v) {
          $parameters->setParameter($k, $v);
        }
      } else {
        throw new InvalidParameterException($this->getName() . " invalid.");
      }
      if (!SpecificationBag::validate($this->specificationBag, $parameters)) {
        throw new InvalidParameterException($this->getName() . " invalid.");
      }
    }
  }




}
