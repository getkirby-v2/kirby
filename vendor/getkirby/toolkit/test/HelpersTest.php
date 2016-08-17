<?php

require_once('lib/bootstrap.php');

class HelpersTest extends PHPUnit_Framework_TestCase {
  public function testInvalid() {

    $data = array(
      'username' => 'homer',
      'email'    => 'homer@simpson.com',
      'zip'      => 0,
      'street'   => ''
    );

    $rules = array(
      'name'     => array('required'), // not set
      'username' => array('required'), // valid
      'zip'      => array('required'), // 0 to see if the empty check works correctly
      'street'   => array('required')  // set but empty
    );

    $messages = array(
      'name'   => 'The name is required',
      'street' => 'The street is required'
    );

    $invalid  = invalid($data, $rules, $messages);    
    $expected = array('name' => 'The name is required', 'street' => 'The street is required');

    $this->assertEquals($expected, $invalid);
        
  }
}