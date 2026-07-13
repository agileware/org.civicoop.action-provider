<?php

namespace Civi\ActionProvider\Parameter;

use Civi\ActionProvider\Exception\InvalidParameterException;
use CRM_ActionProvider_ExtensionUtil as E;

class FileSpecification extends Specification {

  /**
   * @param string $name
   * @param string $title
   * @param bool $required
   */
  public function __construct($name, $title='', $required = false, $multiple = false) {
    $this->setName($name);
    $this->setDataType('String');
    $this->setTitle($title);
    $this->setRequired($required);
    $this->setMultiple($multiple);
  }

  public function validate($value) {
    if (empty($value)) {
      return;
    }
    if (!$this->multiple) {
      $this->validateFile($value);
    } else {
      if (!is_array($value)) {
        throw new InvalidParameterException($this->getName() . ' is invalid. It does not contain a list of files');
      }
      foreach($value as $v) {
        $this->validateFile($v);
      }
    }
  }

  private function validateFile($value) {
    if (!is_array($value)) {
      throw new InvalidParameterException($this->getName() . ' is invalid. The file type expects an array with the keys: name, mime_type, content/url or with an id');
    }
    if (!isset($value['id'])) {
      if (!isset($value['name']) || !isset($value['mime_type']) || (!isset($value['content']) && !isset($value['url']))) {
        throw new InvalidParameterException($this->getName() . ' is invalid. The file type expects an array with the keys: name, mime_type, content/url or with an id');
      }
    }
  }

}
