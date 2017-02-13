<?php

require_once('lib/bootstrap.php');

class HelpersTest extends PHPUnit_Framework_TestCase {

  public function testInvalid()
  {
    $data = [
      'username' => 123,
      'email' => 'homersimpson.com',
      'zip' => 'abc',
      'website' => '',
    ];

    $rules = [
      'username' => ['alpha'],
      'email' => ['required', 'email'],
      'zip' => ['integer'],
      'website' => ['url'],
    ];

    $messages = [
      'username' => 'The username must not contain numbers',
      'email' => 'Invalid email',
      'zip' => 'The ZIP must contain only numbers',
    ];

    $result  = invalid($data, $rules, $messages);
    $this->assertEquals($messages, $result);

    $data = [
      'username' => 'homer',
      'email' => 'homer@simpson.com',
      'zip' => 123,
      'website' => 'http://example.com',
    ];

    $result  = invalid($data, $rules, $messages);
    $this->assertEquals([], $result);
  }

  public function testInvalidSimple()
  {
    $data = ['homer', null];
    $rules = [['alpha'], ['required']];
    $result = invalid($data, $rules);
    $this->assertEquals(1, $result[1]);
  }

  public function testInvalidRequired()
  {
    $rules = ['email' => ['required']];
    $messages = ['email' => ''];

    $result = invalid(['email' => null], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result = invalid(['name' => 'homer'], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result = invalid(['email' => ''], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result = invalid(['email' => []], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result = invalid(['email' => '0'], $rules, $messages);
    $this->assertEquals([], $result);

    $result = invalid(['email' => 0], $rules, $messages);
    $this->assertEquals([], $result);

    $result = invalid(['email' => false], $rules, $messages);
    $this->assertEquals([], $result);

    $result = invalid(['email' => 'homer@simpson.com'], $rules, $messages);
    $this->assertEquals([], $result);
  }

  public function testInvalidOptions()
  {
    $rules = [
      'username' => ['min' => 6]
    ];

    $messages = ['username' => ''];

    $result  = invalid(['username' => 'homer'], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result  = invalid(['username' => 'homersimpson'], $rules, $messages);
    $this->assertEquals([], $result);

    $rules = [
      'username' => ['between' => [3, 6]]
    ];

    $result  = invalid(['username' => 'ho'], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result  = invalid(['username' => 'homersimpson'], $rules, $messages);
    $this->assertEquals($messages, $result);

    $result  = invalid(['username' => 'homer'], $rules, $messages);
    $this->assertEquals([], $result);
  }

  public function testMultipleMessages()
  {
    $data = ['username' => ''];
    $rules = ['username' => ['required', 'alpha', 'min' => 4]];
    $messages = ['username' => [
      'The username is required',
      'The username must contain only letters',
      'The username must be at least 4 characters long',
    ]];

    $result = invalid(['username' => ''], $rules, $messages);
    $expected = ['username' => [
      'The username is required',
    ]];
    $this->assertEquals($expected, $result);

    $result = invalid(['username' => 'a1'], $rules, $messages);
    $expected = ['username' => [
      'The username must contain only letters',
      'The username must be at least 4 characters long',
    ]];
    $this->assertEquals($expected, $result);

    $result = invalid(['username' => 'ab'], $rules, $messages);
    $expected = ['username' => [
      'The username must be at least 4 characters long',
    ]];
    $this->assertEquals($expected, $result);

    $result = invalid(['username' => 'abcd'], $rules, $messages);
    $this->assertEquals([], $result);
  }
}
