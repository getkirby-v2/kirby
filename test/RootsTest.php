<?php

require_once('lib/bootstrap.php');

class RootsTest extends KirbyTestCase {

  public function testDefaults() {

    $roots = new Kirby\Roots(__DIR__);

    // cli index urls are set to a simple slash
    $this->assertEquals(__DIR__, $roots->index());
    $this->assertEquals(__DIR__ . DS . 'content', $roots->content());
    $this->assertEquals(__DIR__ . DS . 'kirby', $roots->kirby());
    $this->assertEquals(__DIR__ . DS . 'site', $roots->site());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'config', $roots->config());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'plugins', $roots->plugins());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'controllers', $roots->controllers());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'widgets', $roots->widgets());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'templates', $roots->templates());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'snippets', $roots->snippets());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'tags', $roots->tags());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'languages', $roots->languages());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'blueprints', $roots->blueprints());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'accounts', $roots->accounts());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'roles', $roots->roles());
    $this->assertEquals(__DIR__ . DS . 'site' . DS . 'cache', $roots->cache());
    $this->assertEquals(__DIR__ . DS . 'assets', $roots->assets());
    $this->assertEquals(__DIR__ . DS . 'assets' . DS . 'css' . DS . 'templates', $roots->autocss());
    $this->assertEquals(__DIR__ . DS . 'assets' . DS . 'js' . DS . 'templates', $roots->autojs());
    $this->assertEquals(__DIR__ . DS . 'assets' . DS . 'avatars', $roots->avatars());
    $this->assertEquals(__DIR__ . DS . 'thumbs', $roots->thumbs());

  }

  public function testModifiedContent() {

    $roots = new Kirby\Roots(__DIR__);
    $roots->content = __DIR__ . DS . 'mycontent';

    $this->assertEquals(__DIR__ . DS . 'mycontent', $roots->content());

  }

  public function testModifiedThumbs() {

    $roots = new Kirby\Roots(__DIR__);
    $roots->thumbs = __DIR__ . DS . 'mythumbs';

    $this->assertEquals(__DIR__ . DS . 'mythumbs', $roots->thumbs());

  }

  public function testModifiedKirby() {

    $roots = new Kirby\Roots(__DIR__);
    $roots->kirby = __DIR__ . DS . 'mykirby';

    $this->assertEquals(__DIR__ . DS . 'mykirby', $roots->kirby());

  }

  public function testModifiedSite() {

    $roots = new Kirby\Roots(__DIR__);
    $roots->site = __DIR__ . DS . 'mysite';

    $this->assertEquals(__DIR__ . DS . 'mysite', $roots->site());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'config', $roots->config());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'plugins', $roots->plugins());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'controllers', $roots->controllers());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'widgets', $roots->widgets());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'templates', $roots->templates());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'snippets', $roots->snippets());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'tags', $roots->tags());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'languages', $roots->languages());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'blueprints', $roots->blueprints());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'accounts', $roots->accounts());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'roles', $roots->roles());
    $this->assertEquals(__DIR__ . DS . 'mysite' . DS . 'cache', $roots->cache());

  }

  public function testModifiedAssets() {

    $roots = new Kirby\Roots(__DIR__);
    $roots->assets = __DIR__ . DS . 'myassets';

    $this->assertEquals(__DIR__ . DS . 'myassets', $roots->assets());
    $this->assertEquals(__DIR__ . DS . 'myassets' . DS . 'css' . DS . 'templates', $roots->autocss());
    $this->assertEquals(__DIR__ . DS . 'myassets' . DS . 'js' . DS . 'templates', $roots->autojs());
    $this->assertEquals(__DIR__ . DS . 'myassets' . DS . 'avatars', $roots->avatars());

  }


}