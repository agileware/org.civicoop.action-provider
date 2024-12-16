<?php
/**
 * For the purposes of this class, sets are supposed to be realised as associative arrays
 * where keys represent the members and and values are ignored.
 *
 * @author Sebastian Lisken <sebastian.lisken@civiservice.de>
 * @license AGPL-3.0
 *
 */

namespace Civi\ActionProvider\Utils;

class SetOps {

  /**
   * Create a set as required by the other functions (where members are stored as keys)
   * from a more standard array (where members are represented by values).
   *
   * @param array $array
   * @return array
   */
  public static function setFromArray($array) {
    return array_fill_keys($array, 1);
  }

  /**
   * Checks whether a set is empty
   *
   * @param $set
   *
   * @return bool
   */
  public static function isEmpty($set) {
    return count($set) == 0;
  }

  /**
   * Checks whether two sets have no common members (their intersection is empty).
   *
   * @param $set1
   * @param $set2
   *
   * @return bool
   */
  public static function disjoint($set1, $set2) {
    if (count($set1) > count($set2)) {
      $largerSet = $set1;
      $smallerSet = $set2;
    } else {
      $largerSet = $set2;
      $smallerSet = $set1;
    }
    $intersectionEmpty = true;
    SetOps::findInMembers($largerSet, $smallerSet, function ($member) use (&$intersectionEmpty) {
      $intersectionEmpty = false;
      return true;
    });
    return $intersectionEmpty;
  }

  /**
   * Checks whether the first given set is a subset of (or equal to) the second.
   *
   * @param $subset
   * @param $superset
   *
   * @return bool
   */
  public static function isSubset($subset, $superset) {
    if (count($subset) > count($superset)) {
      return false;
    }
    $extraMemberFound = false;
    SetOps::findInMembers($superset, $subset, function ($member) use (&$extraMemberFound) {
      $extraMemberFound = true;
      return true;
    }, SetOps::CALL_IF_NOT_FOUND);
    return !$extraMemberFound;
  }

  protected const CALL_IF_FOUND = 1;
  protected const CALL_IF_NOT_FOUND = 2;
  protected const CALL_ALWAYS = 3;

  /**
   * Compares two sets by iterating through one set’s members, searching each the member in the other set,
   * and calling a function, by default if the member was found in the other set. The default can be changed
   * by the optional fourth parameter, the class constants CALL_IF_FOUND, CALL_IF_NOT_FOUND and CALL_ALWAYS
   * can be used for readability.
   *
   * If the callback returns a truthy value, the iteration is stopped.
   *
   * @param $setToFind
   * @param $setToSearch
   * @param $callback
   * @param $call_mode
   */
  protected static function findInMembers($setToSearch, $setToIterate, $callback, $call_mode = SetOps::CALL_IF_FOUND) {
    foreach($setToIterate as $member => $value) {
      $found = array_key_exists($member, $setToSearch);
      if ($call_mode & ($found ? SetOps::CALL_IF_FOUND : SetOps::CALL_IF_NOT_FOUND)) {
        if ($callback($member, $found)) {
          break;
        }
      }
    }
  }

}
