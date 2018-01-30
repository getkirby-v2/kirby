<?php

require_once('lib/bootstrap.php');

class RTest extends PHPUnit_Framework_TestCase {
  
  public function testData() {
    $this->assertTrue(is_array(r::data()));    
  }  

  public function testSet() {

    r::set('testvar', 'testvalue');

    $this->assertEquals('testvalue', r::get('testvar'));

  }

  public function testGet() {

    $this->assertEquals('testvalue', r::get('testvar'));
    $this->assertEquals('defaultvalue', r::get('nonexistent', 'defaultvalue'));

    $this->assertTrue(is_array(r::get()));

  }

  public function testRemove() {
    
    r::remove('testvar');

    $this->assertFalse(isset($_REQUEST['testvar']));
    $this->assertNull(r::get('testvar'));

  }

  public function testMethod() {
    $this->assertEquals('GET', r::method());
  }

  public function testBody() {
    $this->assertEquals('', r::body());
  }

  public function testIs() {
    $this->assertTrue(r::is('GET'));
    $this->assertFalse(r::is('ajax'));
  }

  public function testReferer() {
    $this->assertNull(r::referer());
  }

  public function testIp() {
    $this->assertEquals(false, r::ip());
  }

  public function testCli() {
    $this->assertTrue(r::cli());
  }

  public function testAjax() {
    $this->assertFalse(r::ajax());
  }

  public function testScheme() {
    $this->assertEquals('http', r::scheme());
  }

  public function testSsl() {
    $this->assertFalse(r::ssl());
    $this->assertFalse(r::secure());
  }

}