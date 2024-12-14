<?php

if (!class_exists("\Civi\ActionProvider\Utils\SetOps")) {
  require(__DIR__ . '/../../../../../Civi/ActionProvider/Utils/SetOps.php');
}
use \Civi\ActionProvider\Utils\SetOps;

/**
 * SetOps test case.
 */
class SetOpsTest extends \PHPUnit\Framework\TestCase {

  public function testSetFromArray1() {
    $this->assertEquals([], SetOps::setFromArray([]));
  }

  public function testSetFromArray2() {
    $this->assertEquals(["a" => 1, "b" => 1], SetOps::setFromArray(["a", "b"]));
  }

  public function testSetFromArray3() {
    $this->assertEquals(["a" => 1, "b" => 1], SetOps::setFromArray(["a", "b", "a"]));
  }

  public function testIsEmpty1() {
    $this->assertTrue(SetOps::isEmpty(
      []
      ));
  }

  public function testIsEmpty2() {
    $this->assertFalse(SetOps::isEmpty(
      ["a" => 1]
      ));
  }

  public function testDisjoint1() {
    $this->assertTrue(SetOps::disjoint(
      ["a" => 1, "b" => 1],
      ["c" => 1]
      ));
  }

  public function testDisjoint2() {
    $this->assertTrue(SetOps::disjoint(
      ["a" => 1],
      ["b" => 1, "c" => 1]
      ));
  }

  public function testDisjoint3() {
    $this->assertFalse(SetOps::disjoint(
      ["a" => 1, "b" => 1],
      ["b" => 1]
      ));
  }

  public function testDisjoint4() {
    $this->assertTrue(SetOps::disjoint(
      [],
      ["a" => 1]
      ));
  }

  public function testDisjoint5() {
    $this->assertTrue(SetOps::disjoint(
      [],
      []
      ));
  }

  public function testSubset1() {
    $this->assertTrue(SetOps::isSubset(
      ["a" => 1],
      ["a" => 1, "b" => 1],
      ));
  }

  public function testSubset2() {
    $this->assertFalse(SetOps::isSubset(
      ["a" => 1, "b" => 1],
      ["b" => 1]
      ));
  }

  public function testSubset3() {
    $this->assertFalse(SetOps::isSubset(
      ["b" => 1, "a" => 1],
      ["b" => 1]
      ));
  }

  public function testSubset4() {
    $this->assertTrue(SetOps::isSubset(
      [],
      []
      ));
  }

  public function testSubset5() {
    $this->assertTrue(SetOps::isSubset(
      [],
      ["a" => 1]
      ));
  }

}
