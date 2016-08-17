<?php

require_once('lib/bootstrap.php');

class LTest extends PHPUnit_Framework_TestCase {
  
  public function __construct() {

    l::set('testvar', 'testvalue');

  }

  public function testGet() {
    
    $this->assertEquals('testvalue', l::get('testvar'));
    $this->assertEquals('defaultvalue', l::get('nonexistentvar', 'defaultvalue'));

  }  

  public function testSet() {

    l::set('anothervar', 'anothervalue');
    l::set('testvar', 'overwrittenvalue');

    $this->assertEquals('anothervalue', l::get('anothervar'));
    $this->assertEquals('overwrittenvalue', l::get('testvar'));

    l::set(array(
      'var1' => 'value1',
      'var2' => 'value2'
    ));

    $this->assertEquals('value1', l::get('var1'));
    $this->assertEquals('value2', l::get('var2'));

  }

}