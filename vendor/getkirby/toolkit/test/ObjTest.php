<?php

require_once('lib/bootstrap.php');

class ObjTest extends PHPUnit_Framework_TestCase {

  protected $data;
  protected $object;

  protected function setUp() {
    $this->data = array(
      'username' => 'bastian',
      'email'    => 'bastian@getkirby.com',
      'password' => 'this is so secret', 
    );
    
    $this->object = new Obj($this->data);
  }
  
  public function testInitializeObject() {
    $this->assertInstanceOf('Obj', $this->object);        
  }
  
  public function testGetters() {
    $this->assertEquals('bastian', $this->object->username);
    $this->assertEquals('bastian@getkirby.com', $this->object->email);
    $this->assertEquals('this is so secret', $this->object->password);
        
    $this->assertEquals('bastian', $this->object->username());
    $this->assertEquals('bastian@getkirby.com', $this->object->email());
    $this->assertEquals('this is so secret', $this->object->password());
  }
  
  public function testSetters() {
    $this->object->fullname = 'Bastian Allgeier';
    $this->object->twitter  = '@bastianallgeier';
    
    $this->assertEquals('Bastian Allgeier', $this->object->fullname);
    $this->assertEquals('@bastianallgeier', $this->object->twitter );
    
    $this->assertEquals('Bastian Allgeier', $this->object->get('fullname'));
    $this->assertEquals('@bastianallgeier', $this->object->get('twitter') );
    
    $this->assertEquals('Bastian Allgeier', $this->object->fullname());
    $this->assertEquals('@bastianallgeier', $this->object->twitter() );
    
    // special setting stuff
    $this->object->{15} = 'super test';
    $this->assertEquals('super test', $this->object->{15});
    
    $this->object->_ = 'another super test';
    $this->assertEquals('another super test', $this->object->_);
            
    unset($this->object->username);
    $this->assertFalse(isset($this->object->username));
            
  }
  
}
