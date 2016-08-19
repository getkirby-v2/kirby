<?php

require_once('lib/bootstrap.php');

class ErrorReportingTest extends PHPUnit_Framework_TestCase {
  
  public function testGet() {
    $this->assertEquals(error_reporting(), ErrorReporting::get());
  }
  
  public function testIncludes() {
    // E_ERROR is included in E_ERROR | E_NOTICE
    $this->assertTrue(ErrorReporting::includes(E_ERROR, E_ERROR | E_NOTICE));
    $this->assertTrue(ErrorReporting::includes('E_ERROR', E_ERROR | E_NOTICE));
    $this->assertTrue(ErrorReporting::includes('ERROR', E_ERROR | E_NOTICE));
    $this->assertTrue(ErrorReporting::includes('ErRoR', E_ERROR | E_NOTICE));
    $this->assertTrue(ErrorReporting::includes('eRrOr', E_ERROR | E_NOTICE));
    
    // E_WARNING is not included in E_ERROR | E_NOTICE
    $this->assertFalse(ErrorReporting::includes(E_WARNING, E_ERROR | E_NOTICE));
    $this->assertFalse(ErrorReporting::includes('E_WARNING', E_ERROR | E_NOTICE));
    $this->assertFalse(ErrorReporting::includes('WARNING', E_ERROR | E_NOTICE));
    $this->assertFalse(ErrorReporting::includes('waRNinG', E_ERROR | E_NOTICE));
    
    // E_WARNING is included in E_ALL
    $this->assertTrue(ErrorReporting::includes(E_WARNING, E_ALL));
    
    // test E_ALL values
    $this->assertTrue(ErrorReporting::includes(E_WARNING, E_ALL ^ E_NOTICE));
    $this->assertFalse(ErrorReporting::includes(E_NOTICE, E_ALL ^ E_NOTICE));
  }
  
  /**
   * @expectedException Exception
   */
  public function testIncludesInvalidThrow() {
    ErrorReporting::includes('notexistingforsure');
  }
  
  public function testSet() {
    $before = ErrorReporting::get();
    $after = ErrorReporting::set($before ^ E_ERROR ^ E_WARNING);
    
    $this->assertEquals(ErrorReporting::get(), $after);
    $this->assertNotEquals($before, $after);
    $this->assertEquals($before ^ E_ERROR ^ E_WARNING, $after);
    
    // reset to the real value
    error_reporting($before);
  }
  
  public function testAdd() {
    $reset = ErrorReporting::get();
    
    // normal behavior
    ErrorReporting::set($reset ^ E_NOTICE);
    $before = ErrorReporting::get();
    $success = ErrorReporting::add(E_NOTICE);
    $after = ErrorReporting::get();
    
    $this->assertTrue($success);
    $this->assertEquals(ErrorReporting::get(), $after);
    $this->assertNotEquals($before, $after);
    $this->assertEquals($before | E_NOTICE, $after);
    
    // try to add a level that is already active
    $this->assertFalse(ErrorReporting::add(E_NOTICE));
    
    // reset to the real value
    error_reporting($reset);
  }
  
  public function testRemove() {
    $reset = ErrorReporting::get();
    
    // normal behavior
    ErrorReporting::set($reset | E_ERROR);
    $before = ErrorReporting::get();
    $success = ErrorReporting::remove(E_ERROR);
    $after = ErrorReporting::get();
    
    $this->assertTrue($success);
    $this->assertEquals(ErrorReporting::get(), $after);
    $this->assertNotEquals($before, $after);
    $this->assertEquals($before ^ E_ERROR, $after);
    
    // try to remove a level that is not active
    $this->assertFalse(ErrorReporting::remove(E_ERROR));
    
    // reset to the real value
    error_reporting($reset);
  }
}
