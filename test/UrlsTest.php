<?php

require_once('lib/bootstrap.php');

class UrlsTest extends KirbyTestCase {

  public function testDefaults() {

    $urls = new Kirby\Urls();

    // cli index urls are set to a simple slash
    $this->assertEquals('/', $urls->index());
    $this->assertEquals('/content', $urls->content());
    $this->assertEquals('/thumbs', $urls->thumbs());
    $this->assertEquals('/assets', $urls->assets());
    $this->assertEquals('/assets/avatars', $urls->avatars());
    $this->assertEquals('/assets/css/templates', $urls->autocss());
    $this->assertEquals('/assets/js/templates', $urls->autojs());

  }

  public function testModifiedIndex() {

    $urls = new Kirby\Urls();
    $urls->index = 'http://testdomain.com';

    $this->assertEquals('http://testdomain.com', $urls->index());
    $this->assertEquals('http://testdomain.com/content', $urls->content());
    $this->assertEquals('http://testdomain.com/thumbs', $urls->thumbs());
    $this->assertEquals('http://testdomain.com/assets', $urls->assets());
    $this->assertEquals('http://testdomain.com/assets/avatars', $urls->avatars());
    $this->assertEquals('http://testdomain.com/assets/css/templates', $urls->autocss());
    $this->assertEquals('http://testdomain.com/assets/js/templates', $urls->autojs());

  }
  
  public function testModifiedContent() {

    $urls = new Kirby\Urls();
    $urls->content = '/mycontent';

    $this->assertEquals('/mycontent', $urls->content());

  }

  public function testModifiedAssets() {

    $urls = new Kirby\Urls();
    $urls->assets = 'http://assets.mycdn.com';

    $this->assertEquals('http://assets.mycdn.com', $urls->assets());
    $this->assertEquals('http://assets.mycdn.com/avatars', $urls->avatars());
    $this->assertEquals('http://assets.mycdn.com/css/templates', $urls->autocss());
    $this->assertEquals('http://assets.mycdn.com/js/templates', $urls->autojs());

  }

  public function testModifiedThumbs() {

    $urls = new Kirby\Urls();
    $urls->thumbs = 'http://thumbs.mycdn.com';

    $this->assertEquals('http://thumbs.mycdn.com', $urls->thumbs());

  }

}