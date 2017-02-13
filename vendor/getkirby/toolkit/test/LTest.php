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

  public function testFormatter() {

    l::set('message', '{number, number}');

    // with default locale (en_US)
    $this->assertEquals('1,000', l::get('message', ['number' => 1000]));

    // with custom locale
    $this->assertEquals('1.000', l::get('message', ['number' => 1000], 'de_DE'));

    // with custom global locale
    l::$locale = 'de_DE';
    $this->assertEquals('1.000', l::get('message', ['number' => 1000]));

  }

}