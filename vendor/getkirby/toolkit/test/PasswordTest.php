<?php

require_once('lib/bootstrap.php');

class PasswordTest extends PHPUnit_Framework_TestCase {

  protected $pw;
  protected $hash;

  protected function setUp() {

    $this->pw   = 'mysupersecretpassword';
    $this->hash = Password::hash($this->pw);

  }

  public function testHash() {
    $this->assertTrue(is_string($this->hash));
  }

  public function testMatch() {

    $this->assertTrue(Password::match($this->pw, $this->hash));
    $this->assertFalse(Password::match('myincorrectpassword', $this->hash));

  }

}
