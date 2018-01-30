<?php

require_once('lib/bootstrap.php');

class UrlTest extends PHPUnit_Framework_TestCase {
  
  public function testHasQuery() {

    $this->assertTrue(url::hasQuery('http://getkirby.com/?search=some'));
    $this->assertFalse(url::hasQuery('http://getkirby.com/docs/support'));

  }

  public function testIsAbsolute() {

    $this->assertTrue(url::isAbsolute('http://example.com/some-uri'));
    $this->assertTrue(url::isAbsolute('https://example.com/some-uri'));
    $this->assertTrue(url::isAbsolute('ftp://example.com/some-uri'));
    $this->assertTrue(url::isAbsolute('some-other+123-proto.col://example.com/some-uri'));
    $this->assertTrue(url::isAbsolute('//example.com/some-uri'));
    $this->assertTrue(url::isAbsolute('mailto:example@example.com'));

    $this->assertFalse(url::isAbsolute(''));
    $this->assertFalse(url::isAbsolute('some-uri'));
    $this->assertFalse(url::isAbsolute('/some-uri'));
    $this->assertFalse(url::isAbsolute('/some-uri/?url=https://example.com'));

  }

  public function testBuild() {

    $this->assertEquals('http://getkirby.com/', url::build([], 'http://getkirby.com'));
    $this->assertEquals('https://getkirby.com/', url::build([
      'scheme' => 'https'
    ], 'http://getkirby.com'));
    $this->assertEquals('http://twitter.com/', url::build([
      'host' => 'twitter.com'
    ], 'http://getkirby.com'));
    $this->assertEquals('https://twitter.com/', url::build([
      'scheme' => 'https',
      'host'   => 'twitter.com'
    ], 'http://getkirby.com'));

    $this->assertEquals('https://getkirby.com/panel', url::build([], 'https://getkirby.com/panel'));
    $this->assertEquals('https://getkirby.com/panel/', url::build([], 'https://getkirby.com/panel/'));

    $this->assertEquals('https://getkirby.com/search/page:2?q=something', url::build([
      'params' => ['page' => 2]
    ], 'https://getkirby.com/search?q=something'));
    $this->assertEquals('https://getkirby.com/search/page:2?q=something', url::build([
      'params' => ['page' => 2]
    ], 'https://getkirby.com/search/?q=something'));
    $this->assertEquals('https://getkirby.com/search/page:3?q=something', url::build([
      'params' => ['page' => 3]
    ], 'https://getkirby.com/search/page:2?q=something'));
    $this->assertEquals('https://getkirby.com/search/?q=something', url::build([
      'params' => []
    ], 'https://getkirby.com/search/?q=something'));
    $this->assertEquals('https://getkirby.com/search?q=something', url::build([
      'params' => []
    ], 'https://getkirby.com/search?q=something'));
    $this->assertEquals('https://getkirby.com/search?q=something', url::build([
      'params' => []
    ], 'https://getkirby.com/search/page:2?q=something'));

    $this->assertEquals('https://getkirby.com/some/url/with:params?and=query#hash', url::build([], 'https://getkirby.com/some/url/with:params?and=query#hash'));
    $this->assertEquals('https://getkirby.com/some/otherurl/with:otherparams/and:moreparams?anddifferent=query#differenthash', url::build([
      'fragments' => ['some', 'otherurl'],
      'params'    => ['with' => 'otherparams', 'and' => 'moreparams'],
      'query'     => ['anddifferent' => 'query'],
      'hash'      => 'differenthash'
    ], 'https://getkirby.com/some/url/with:params?and=query#hash'));

  }

}