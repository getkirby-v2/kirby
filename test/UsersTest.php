<?php

require_once('lib/bootstrap.php');

class UsersTest extends KirbyTestCase {

  public function testConstruction() {
    $site  = $this->siteInstance();

    $this->assertInstanceOf('Users', $site->users());
    $this->assertEquals(1, $site->users()->count());
    $this->assertInstanceOf('User', $site->users()->first());
  }

  public function testCreate() {
    $site  = $this->siteInstance();

    try {
      $site->users()->create(array(
        'username'  => 'john',
        'email'     => 'john@doe.com',
        'password'  => 'secretpasswordwillbeencrypted',
        'firstName' => 'John',
        'lastName'  => 'Doe'
      ));
    } catch(Exception $e) {
      echo $e->getMessage();
    }
    $this->assertInstanceOf('User', $site->user('john'));
    $this->assertEquals(2, $site->users()->count());
    $this->assertEquals('john@doe.com', $site->user('john')->email());
    $this->assertEquals('John', $site->user('john')->firstName());
    $this->assertEquals('Doe', $site->user('john')->lastName());

  }

  public function testUpdate() {
    $site  = $this->siteInstance();

    $this->assertInstanceOf('User', $site->user('john'));
    try {
      $site->user('john')->update(array(
        'username'  => 'john',
        'email'     => 'jane@doe.com',
        'firstName' => 'Jane',
      ));
    } catch(Exception $e) {
      echo $e->getMessage();
    }
    $this->assertEquals('jane@doe.com', $site->user('john')->email());
    $this->assertEquals('Jane', $site->user('john')->firstName());
  }

  public function testDelete() {
    $site  = $this->siteInstance();
    try {
      $site->user('john')->delete();
    } catch(Exception $e) {
      echo $e->getMessage();
    }
    $this->assertEquals(1, $site->users()->count());
  }

}
