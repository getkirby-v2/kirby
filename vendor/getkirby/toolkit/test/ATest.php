<?php

require_once('lib/bootstrap.php');

class ATest extends PHPUnit_Framework_TestCase {
  
  public function __construct() {

    $this->user = array(
      'username' => 'testuser',
      'password' => 'testpassword',
      'email'    => 'test@user.com'
    );

    $this->users = array();

    $this->users['userA'] = $this->user;
    $this->users['userA']['username'] = 'peter';

    $this->users['userB'] = $this->user;
    $this->users['userB']['username'] = 'paul';

    $this->users['userC'] = $this->user;
    $this->users['userC']['username'] = 'mary';

  }

  public function testGet() {

    $this->assertEquals('testuser', a::get($this->user, 'username'));

    // get an array of keys
    $array = a::get($this->user, array('username', 'email'));

    $this->assertEquals(array('username' => 'testuser', 'email' => 'test@user.com'), $array);

  }

  public function testShow() {
    // not really testable
  }

  public function testJson() {
    $this->assertEquals(json_encode($this->user), a::json($this->user));
  }

  public function testXml() {
    // to be tested in the x class
  }

  public function testExtract() {

    $users = $this->users;
    
    $usernames = a::extract($users, 'username');
    $this->assertEquals(array('peter', 'paul', 'mary'), $usernames);

  }

  public function testShuffle() {
    
    $users = $this->users;
    $users = a::shuffle($users);

    // the only testable thing is that keys still exist
    $this->assertTrue(isset($users['userA']));

  }

  public function testFirst() {

    $users = $this->users;
    $user = a::first($users);

    $this->assertEquals('peter', $user['username']);

  }

  public function testLast() {

    $users = $this->users;
    $user = a::last($users);

    $this->assertEquals('mary', $user['username']);

  }

  public function testFill() {

    $users = $this->users;
    $users = a::fill($users, 100);

    $this->assertEquals(100, count($users));

  }

  public function testMissing() {

    $user = $this->user;
    $required = array('username', 'password', 'website');

    $missing = a::missing($user, $required);

    $this->assertEquals(array('website'), $missing);

  }

  public function testSort() {

    $users = $this->users;
    $users = a::sort($users, 'username', 'asc');
    $first = a::first($users);
    $last  = a::last($users);

    $this->assertEquals('mary', $first['username']);
    $this->assertEquals('peter', $last['username']);

  }

  public function testUpdate() {

    // original data
    $source = [
      'a' => 'value a',
      'b' => 'value b'
    ];

    // test with simple array
    $result = a::update($source, [
      'a' => 'updated value a',
      'c' => 'new value c'
    ]);

    $this->assertEquals('updated value a', $result['a']);
    $this->assertEquals('value b', $result['b']);
    $this->assertEquals('new value c', $result['c']);

    // test with callbacks
    $result = a::update($source, [
      'a' => function($value) {
        return 'updated ' . $value;
      },
      'c' => function($value) {
        return 'new value c';
      }
    ]);

    $this->assertEquals('updated value a', $result['a']);
    $this->assertEquals('value b', $result['b']);
    $this->assertEquals('new value c', $result['c']);

  }

}