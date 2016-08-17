<?php

require_once('lib/bootstrap.php');

class BitmaskTest extends PHPUnit_Framework_TestCase {
  
  public function testValidValue() {
    // valid values
    $this->assertTrue(Bitmask::validValue(1));
    $this->assertTrue(Bitmask::validValue(2));
    $this->assertTrue(Bitmask::validValue(4));
    $this->assertTrue(Bitmask::validValue(64));
    $this->assertTrue(Bitmask::validValue(256));
    $this->assertTrue(Bitmask::validValue(32768));
    $this->assertTrue(Bitmask::validValue(9007199254740992));
    
    // invalid values
    $this->assertFalse(Bitmask::validValue(10));
    $this->assertFalse(Bitmask::validValue(13));
    $this->assertFalse(Bitmask::validValue(4.2));
    $this->assertFalse(Bitmask::validValue('string'));
    $this->assertFalse(Bitmask::validValue(array()));
  }
  
  public function testIncludes() {
    $mask = 1 | 4 | 32;
    
    // existing values
    $this->assertTrue(Bitmask::includes(1, $mask));
    $this->assertTrue(Bitmask::includes(4, $mask));
    $this->assertTrue(Bitmask::includes(32, $mask));
    
    // non-existing values
    $this->assertFalse(Bitmask::includes(2, $mask));
    $this->assertFalse(Bitmask::includes(16, $mask));
    
    // invalid values
    $this->assertFalse(Bitmask::includes(array(), $mask));
    $this->assertFalse(Bitmask::includes('string', $mask));
    $this->assertFalse(Bitmask::includes(13, $mask));
  }
  
  public function testAdd() {
    $mask = 1 | 4 | 32;
    
    $this->assertEquals(Bitmask::add(16, $mask), $mask | 16);
    $this->assertEquals(Bitmask::add(4, $mask), $mask);
  }
  
  /**
   * @expectedException Exception
   */
  public function testAddInvalidThrow() {
    Bitmask::add(42, 1);
  }
  
  public function testRemove() {
    $mask = 1 | 4 | 32;
    
    $this->assertEquals(Bitmask::remove(32, $mask), $mask ^ 32);
    $this->assertEquals(Bitmask::remove(16, $mask), $mask);
  }
  
  /**
   * @expectedException Exception
   */
  public function testRemoveInvalidThrow() {
    Bitmask::remove(42, 1);
  }
}
