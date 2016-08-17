<?php

require_once('lib/bootstrap.php');

class ServerTest extends PHPUnit_Framework_TestCase {
  
  public function testGet() {

    $this->assertTrue(is_array(server::get()));
    $this->assertEquals($_SERVER, server::get());

  }

  public function testSanitization() {

    $_SERVER['HTTP_HOST']   = '<script>alert("xss")</script>';
    $_SERVER['SERVER_NAME'] = '<script>alert("xss")</script>';

    $this->assertEquals('alertxss', server::get('HTTP_HOST'));
    $this->assertEquals('alertxss', server::get('SERVER_NAME'));

    $_SERVER['HTTP_HOST'] = '127.0.0.1';
    $_SERVER['SERVER_NAME'] = '127.0.0.1';

    $this->assertEquals('127.0.0.1', server::get('HTTP_HOST'));
    $this->assertEquals('127.0.0.1', server::get('SERVER_NAME'));

    $_SERVER['SERVER_PORT'] = '<script>alert("xss")</script>999';

    $this->assertEquals('999', server::get('SERVER_PORT'));

  }

}