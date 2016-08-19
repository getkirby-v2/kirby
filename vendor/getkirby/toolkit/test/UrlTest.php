<?php

require_once('lib/bootstrap.php');

class UrlTest extends PHPUnit_Framework_TestCase {
  
  public function testHasQuery() {

    $this->assertTrue(url::hasQuery('http://getkirby.com/?search=some'));
    $this->assertFalse(url::hasQuery('http://getkirby.com/docs/support'));

  }


}