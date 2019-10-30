<?php
/**
 * @author Jaap Jansma <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

namespace Civi\ActionProvider\Parameter;

interface SpecificationInterface {

  /**
   * @return string
   */
  public function getName();

  /**
   * @return string
   */
  public function getTitle();

  /**
   * @return string
   */
  public function getDescription();

  /**
   * @return array
   */
  public function toArray();

  /**
   * @return String
   */
  public function getType();

  /**
   * @param $value
   * @return bool
   * @throws \Civi\ActionProvider\Exception\InvalidParameterException
   */
  public function validate($value);

}
