<?php

require_once('lib/bootstrap.php');

class SystemTest extends PHPUnit_Framework_TestCase {
  
  public function testIsExecutable() {
    // basic stuff
    $this->assertTrue(System::isExecutable('bash'));
    $this->assertFalse(System::isExecutable('somethingtotallynotexisting'));
    
    // with path
    $this->assertTrue(System::isExecutable('/bin/bash'));
    
    // with additional arguments
    $this->assertTrue(System::isExecutable('bash something totally strange and this should be ignored'));
    $this->assertFalse(System::isExecutable('somethingtotallynotexisting something totally strange and this should be ignored'));
    
    // executable files not in $PATH
    $this->assertTrue(System::isExecutable(TEST_ROOT_ETC . '/system/executable.sh'));
    $this->assertTrue(System::isExecutable(TEST_ROOT_ETC . '/system/executable.sh something totally strange and this should be ignored'));
    
    // non-executable files
    $this->assertFileExists(TEST_ROOT_ETC . '/system/nonexecutable.sh');
    $this->assertFalse(is_executable(TEST_ROOT_ETC . '/system/nonexecutable.sh'));
    $this->assertFalse(System::isExecutable(TEST_ROOT_ETC . '/system/nonexecutable.sh'));
    
    // invalid files
    $this->assertFalse(System::isExecutable(TEST_ROOT_ETC . '/system/notexisting.sh'));
  }
  
  public function testExecute() {
    // execute an existing system task
    $this->assertEquals(array('output' => 'Hello World', 'status' => 0, 'success' => true), System::execute('echo', 'Hello World'));
    
    // execute the dummy script
    $this->assertEquals(array('output' => 'Some dummy output just to test execution of this file.', 'status' => 0, 'success' => true), System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('totallywayne')));
    
    // other arguments
    $this->assertEquals(array('output' => 'Something is sometimes not that cool. But anyway.', 'status' => 0, 'success' => true), System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('something')));
    $this->assertEquals(array('output' => 'This probably failed. Or so.', 'status' => 42, 'success' => false), System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('fail')));
    
    // other return values
    $this->assertEquals(array('output' => 'Some dummy output just to test execution of this file.', 'status' => 0, 'success' => true), System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('totallywayne'), 'all'));
    $this->assertEquals(array('output' => 'Some dummy output just to test execution of this file.', 'status' => 0, 'success' => true), System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('totallywayne'), 'notexistingforsure'));
    $this->assertEquals('Some dummy output just to test execution of this file.', System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('totallywayne'), 'output'));
    $this->assertEquals(0, System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('totallywayne'), 'status'));
    $this->assertEquals(true, System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('totallywayne'), 'success'));
    
    // other ways of calling
    $this->assertEquals(System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('something'), 'all'), System::execute(TEST_ROOT_ETC . '/system/executable.sh', 'something'));
    $this->assertEquals(System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('something'), 'all'), System::execute(array(TEST_ROOT_ETC . '/system/executable.sh', 'something')));
    $this->assertEquals(System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('something'), 'all'), System::execute(array(TEST_ROOT_ETC . '/system/executable.sh', 'something'), 'all'));
    $this->assertEquals(System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('something'), 'success'), System::execute(array(TEST_ROOT_ETC . '/system/executable.sh', 'something'), 'success'));
    $this->assertEquals(System::execute(TEST_ROOT_ETC . '/system/executable.sh', array('fail'), 'success'), System::execute(array(TEST_ROOT_ETC . '/system/executable.sh', 'fail'), 'success'));
  }
  
  /**
   * @expectedException Exception
   */
  public function testExecuteInvalidThrow() {
    System::execute('notexistingforsure');
  }
  
  public function testCallStatic() {
    $filename = TEST_ROOT_ETC . '/system/executable.sh';
    
    // call the appropriate method statically
    $this->assertEquals(System::execute($filename, array('something'), 'all'), System::$filename('something'));
    $this->assertEquals(System::execute($filename, array('something'), 'all'), System::$filename('something', 'success'));
    $this->assertNotEquals(System::execute($filename, array('something'), 'success'), System::$filename('something', 'success'));
  }
  
  public function testRealpath() {
    // basic stuff
    $this->assertEquals('/bin/sh', System::realpath('sh'));
    $this->assertEquals(false, System::realpath('notexistingforsure'));
    
    // executable file
    $this->assertEquals(realpath(TEST_ROOT_ETC . '/system/executable.sh'), System::realpath(TEST_ROOT_ETC . '/system/executable.sh'));
    
    // not executable file
    $this->assertFileExists(TEST_ROOT_ETC . '/system/nonexecutable.sh');
    $this->assertEquals(false, System::realpath(TEST_ROOT_ETC . '/system/nonexecutable.sh'));
    
    // not existing file
    $this->assertEquals(false, System::realpath(TEST_ROOT_ETC . '/system/notexisting.sh'));
  }
}
