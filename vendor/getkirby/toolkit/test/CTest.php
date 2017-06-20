<?php

require_once('lib/bootstrap.php');

class CTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {

    c::set('testvar', 'testvalue');

  }

  public function testGet() {
    
    $this->assertEquals('testvalue', c::get('testvar'));
    $this->assertEquals('defaultvalue', c::get('nonexistentvar', 'defaultvalue'));

  }  

  public function testSet() {

    c::set('anothervar', 'anothervalue');
    c::set('testvar', 'overwrittenvalue');

    $this->assertEquals('anothervalue', c::get('anothervar'));
    $this->assertEquals('overwrittenvalue', c::get('testvar'));

    c::set(array(
      'var1' => 'value1',
      'var2' => 'value2'
    ));

    $this->assertEquals('value1', c::get('var1'));
    $this->assertEquals('value2', c::get('var2'));

  }
  
}
