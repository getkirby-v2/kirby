<?php

require_once('lib/bootstrap.php');

class UrlTest extends PHPUnit_Framework_TestCase {
  
  public function testHasQuery() {

    $this->assertTrue(url::hasQuery('http://getkirby.com/?search=some'));
    $this->assertFalse(url::hasQuery('http://getkirby.com/docs/support'));

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

    $this->assertEquals('https://getkirby.com/some/url/with:params?and=query#hash', url::build([], 'https://getkirby.com/some/url/with:params?and=query#hash'));
    $this->assertEquals('https://getkirby.com/some/otherurl/with:otherparams/and:moreparams?anddifferent=query#differenthash', url::build([
      'fragments' => ['some', 'otherurl'],
      'params'    => ['with' => 'otherparams', 'and' => 'moreparams'],
      'query'     => ['anddifferent' => 'query'],
      'hash'      => 'differenthash'
    ], 'https://getkirby.com/some/url/with:params?and=query#hash'));

  }

}