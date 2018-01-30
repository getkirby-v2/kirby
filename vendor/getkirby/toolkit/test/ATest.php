<?php

require_once('lib/bootstrap.php');

class ATest extends PHPUnit_Framework_TestCase {

  protected $user;
  protected $users;
  
  protected function setUp() {

    $this->user = array(
      'username' => 'testuser',
      'password' => 'testpassword',
      'email'    => 'test@user.com',
      'image'    => null,
      'logins'   => 0
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

  public function testJson() {
    $this->assertEquals(json_encode($this->user), a::json($this->user));
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
    $required = array('username', 'password', 'website', 'image', 'logins');

    $missing = a::missing($user, $required);

    $this->assertEquals(array('website', 'image'), $missing);

  }

  public function testSort() {

    $users = $this->users;
    $users = a::sort($users, 'username', 'asc');
    $first = a::first($users);
    $last  = a::last($users);

    $this->assertEquals('mary', $first['username']);
    $this->assertEquals('peter', $last['username']);

  }

  public function testMerge() {

    // simple non-associative arrays
    $this->assertEquals(['a', 'b', 'c', 'd'],      a::merge(['a', 'b'], ['c', 'd']));
    $this->assertEquals(['a', 'b', 'c', 'd', 'a'], a::merge(['a', 'b'], ['c', 'd', 'a']));

    // simple associative arrays
    $this->assertEquals(['a' => 'b', 'c' => 'd'], a::merge(['a' => 'b'], ['c' => 'd']));
    $this->assertEquals(['a' => 'c'],             a::merge(['a' => 'b'], ['a' => 'c']));
    $this->assertEquals(['a' => 'd'],             a::merge(['a' => 'b'], ['a' => 'c', 'a' => 'd']));

    // recursive merging
    $this->assertEquals(['a' => ['b', 'c', 'b', 'd']],     a::merge(['a' => ['b', 'c']], ['a' => ['b', 'd']]));
    $this->assertEquals(['a' => ['b' => 'd', 'd' => 'e']], a::merge(['a' => ['b' => 'c', 'd' => 'e']], ['a' => ['b' => 'd']]));
    $this->assertEquals(['a' => ['b', 'c']],               a::merge(['a' => 'b'], ['a' => ['b', 'c']]));
    $this->assertEquals(['a' => 'b'],                      a::merge(['a' => ['b', 'c']], ['a' => 'b']));

    // append feature
    $this->assertEquals(['a', 'b', 'c', 'd', 'a'],                         a::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a']));
    $this->assertEquals(['a', 'b', 'c', 'd', 'a'],                         a::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a'], true));
    $this->assertEquals([1 => 'c', 3 => 'd', 4 => 'b', 5 => 'a'],          a::merge([1 => 'a', 4 => 'b'], [1 => 'c', 3 => 'd', 5 => 'a'], false));
    $this->assertEquals(['a' => ['b', 'c', 'e', 'd']],                     a::merge(['a' => [1 => 'b', 4 => 'c']], ['a' => [1 => 'e', 3 => 'd']], true));
    $this->assertEquals(['a' => [1 => 'c', 3 => 'd', 4 => 'b', 5 => 'a']], a::merge(['a' => [1 => 'a', 4 => 'b']], ['a' => [1 => 'c', 3 => 'd', 5 => 'a']], false));

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
