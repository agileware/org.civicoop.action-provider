<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Parameter;

use Civi\ActionProvider\Exception\InvalidParameterException;

class SpecificationGroup implements SpecificationInterface {

  /**
   * @var SpecificationBag
   */
  protected $specificationBag;

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
  public function __construct($name, $title,  SpecificationBag $specificationBag, $description='') {
    $this->setName($name);
    $this->setTitle($title);
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
    return 'group';
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
   * @param $value
   * @return bool
   * @throws \CRM_Core_Exception
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  public function validate($value) {
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

  public function isRequired() {
    return false;
  }


}
