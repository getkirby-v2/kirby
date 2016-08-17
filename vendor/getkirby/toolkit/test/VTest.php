<?php

require_once('lib/bootstrap.php');

class VTest extends PHPUnit_Framework_TestCase {
  
  public function testMatch() {

    $value = 'super-09';

    $this->assertTrue(v::match($value, '/[a-z0-9-]+/i'));

    $value = '#1asklajd.12jaxax';

    $this->assertFalse(v::match($value, '/^[a-z0-9-]+$/i'));

  }

  public function testSame() {

    $this->assertTrue(v::same('same same but different', 'same same but different'));
    $this->assertFalse(v::same('same same but different', 'same same but diffrent'));

  }

  public function testDifferent() {

    $this->assertFalse(v::different('same same but different', 'same same but different'));
    $this->assertTrue(v::different('same same but different', 'same same but diffrent'));

  }

  public function testDate() {

    $this->assertTrue(v::date('2012-12-12'));
    $this->assertTrue(v::date('1970-01-01'));
    $this->assertFalse(v::date('2013-13-13'));

  }

  public function testEmail() {

    $this->assertTrue(v::email('bastian@getkirby.com'));
    $this->assertFalse(v::email('http://getkirby.com'));

  }

  public function testFilename() {

    $this->assertTrue(v::filename('my-awesome-image@2x.jpg'));
    $this->assertFalse(v::filename('my_fucked!up#image.jpg'));

  }

  public function testAccepted() {

    $this->assertTrue(v::accepted('on'));
    $this->assertTrue(v::accepted('yes'));
    $this->assertTrue(v::accepted('1'));
    $this->assertFalse(v::accepted('no'));

  }

  public function testMin() {

    $this->assertTrue(v::min('superstring', 5));
    $this->assertFalse(v::min('superstring', 20));

    $this->assertTrue(v::min(6, 5));
    $this->assertFalse(v::min(6, 20));

    $this->assertTrue(v::min(range(0,10), 5));
    $this->assertFalse(v::min(range(0,10), 20));

  }

  public function testMax() {

    $this->assertTrue(v::max('superstring', 11));
    $this->assertFalse(v::max('superstring', 5));

    $this->assertTrue(v::max(6, 11));
    $this->assertFalse(v::max(6, 5));

    $this->assertTrue(v::max(range(0,10), 11));
    $this->assertFalse(v::max(range(0,10), 5));

  }

  public function testBetween() {

    $this->assertTrue(v::between('superstring', 5, 11));
    $this->assertFalse(v::between('superstring', 3, 5));

    $this->assertTrue(v::between(6, 5, 11));
    $this->assertFalse(v::between(6, 3, 5));

    $this->assertTrue(v::between(range(0,10), 5, 11));
    $this->assertFalse(v::between(range(0,10), 3, 5));

  }

  public function testIn() {
    $this->assertTrue(v::in('a', array('a', 'b', 'c')));
    $this->assertFalse(v::in('a', array('b', 'c', 'd')));
  }

  public function testNotIn() {
    $this->assertTrue(v::notIn('a', array('b', 'c', 'd')));
    $this->assertFalse(v::notIn('a', array('a', 'b', 'c')));
  }

  public function testIp() {
    $this->assertTrue(v::ip('127.0.0.1'));
    $this->assertFalse(v::ip('not an ip'));
  }

  public function testAlpha() {
    $this->assertTrue(v::alpha('abc'));
    $this->assertFalse(v::alpha('1234'));
  }

  public function testNum() {
    $this->assertTrue(v::num('1234'));
    $this->assertFalse(v::num('abc'));
  }

  public function testAlphaNum() {
    $this->assertTrue(v::alphanum('abc1234'));
    $this->assertFalse(v::alphanum('#!asdas'));
  }

  public function testInteger() {
    $this->assertTrue(v::integer('1234'));
    $this->assertFalse(v::integer('0.1'));
  }

  public function testSize() {
    $this->assertTrue(v::size('super', 5));
    $this->assertTrue(v::size('1234', 1234));
    $this->assertTrue(v::size(range(0,9), 10));
  }

}