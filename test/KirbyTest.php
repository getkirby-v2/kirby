<?php

require_once('lib/bootstrap.php');

class KirbyTest extends KirbyTestCase {

  public function testConstruction() {

    $kirby = $this->kirbyInstance();

    $this->assertInstanceOf('Kirby\\Roots', $kirby->roots());
    $this->assertInstanceOf('Kirby\\Urls',  $kirby->urls());
    $this->assertEquals($kirby->defaults(), $kirby->options());
    $this->assertEquals('', $kirby->path());

  } 

  public function testOptions() {

    $kirby = $this->kirbyInstance();

    $this->assertEquals('UTC', $kirby->option('timezone'));
    $this->assertEquals(null, $kirby->option('license'));
    $this->assertTrue($kirby->option('rewrite'));
    $this->assertEquals('error', $kirby->option('error'));
    $this->assertEquals('home', $kirby->option('home'));
    $this->assertEquals('en_US.UTF8', $kirby->option('locale'));
    $this->assertEquals(array(), $kirby->option('routes'));
    $this->assertEquals(array(), $kirby->option('headers'));
    $this->assertEquals(array(), $kirby->option('languages'));
    $this->assertEquals(array(), $kirby->option('roles'));
    $this->assertFalse($kirby->option('cache'));
    $this->assertEquals('env', $kirby->option('debug'));
    $this->assertFalse($kirby->option('ssl'));
    $this->assertEquals('file', $kirby->option('cache.driver'));
    $this->assertEquals(array(), $kirby->option('cache.options'));
    $this->assertEquals(array(), $kirby->option('cache.ignore'));
    $this->assertTrue($kirby->option('cache.autoupdate'));
    $this->assertEquals('video', $kirby->option('kirbytext.video.class'));
    $this->assertFalse($kirby->option('kirbytext.video.width'));
    $this->assertFalse($kirby->option('kirbytext.video.height'));
    $this->assertEquals('txt', $kirby->option('content.file.extension'));
    $this->assertEquals(array(), $kirby->option('content.file.ignore'));

  }

  public function testInstanceConfiguration() {

    $kirby = $this->kirbyInstance(array(
      'timezone' => 'Europe/Berlin'
    ));

    $this->assertEquals('Europe/Berlin', $kirby->option('timezone'));

  }

  public function testUrl() {

    $kirby = $this->kirbyInstance();

    $this->assertEquals('/', $kirby->urls()->index());

    $this->assertEquals('/', $kirby->url());

    $kirby->urls->index('http://getkirby.com');

    $this->assertEquals('http://getkirby.com', $kirby->urls()->index());

    $kirby->urls->index('http://getkirby.com/');

    $this->assertEquals('http://getkirby.com', $kirby->urls()->index());

    $kirby->url('http://getkirby.com/');

    $this->assertEquals('http://getkirby.com', $kirby->url());

    // test with the old url config option
    $kirby = $this->kirbyInstance(array(
      'url' => '/'
    ));

    // run configure to apply the url option
    $kirby->configure();

    $this->assertEquals('/', $kirby->urls()->index());

    // test with the old url config option
    $kirby = $this->kirbyInstance(array(
      'url' => 'http://getkirby.com'
    ));

    // run configure to apply the url option
    $kirby->configure();

    $this->assertEquals('http://getkirby.com', $kirby->urls()->index());

    // @see UrlsTest.php for more tests of the Urls class.

  }

  public function testInstance() {

    $kirby = $this->kirbyInstance();

    $this->assertEquals($kirby, kirby::instance());

  }

  public function testPathManipulation() {

    $kirby = $this->kirbyInstance();

    $kirby->path = $path = 'blog/article-xy';
    $this->assertEquals($path, $kirby->path);
    $this->assertEquals($path, $kirby->path());

  }

  public function testCacheSetup() {

    $kirby = $this->kirbyInstance();
    $kirby->roots->site = TEST_ROOT_ETC . DS . 'site';

    // disabled cache
    $this->assertInstanceOf('Cache\\Driver\\Mock', $kirby->cache());

    // switch to file cache
    $kirby = $this->kirbyInstance(array(
      'cache'        => true,
      'cache.driver' => 'file'
    ));

    $kirby->roots->site = TEST_ROOT_ETC . DS . 'site';

    $this->assertInstanceOf('Cache\\Driver\\File', $kirby->cache());

  }

  public function testConfigure() {

    $kirby = $this->kirbyInstance();

    // point to a non existing site directory
    // to load the default configuration
    $kirby->roots->site = TEST_ROOT_ETC . DS . 'mysite';    
    $kirby->configure();

    $this->assertEquals($kirby->defaults(), $kirby->options());
    $this->assertEquals(url::$home, $kirby->urls()->index());

    $kirby = $this->kirbyInstance();
    // load a custom config file
    $kirby->roots->site = TEST_ROOT_ETC . DS . 'site';    
    $kirby->configure();

    $this->assertEquals('test', $kirby->option('license'));

  }

  public function testPlugins() {

    $kirby = $this->kirbyInstance();
    $kirby->roots->site = TEST_ROOT_ETC . DS . 'site';

    $plugins = $kirby->plugins();

    $this->assertTrue(isset($plugins['a']));
    $this->assertTrue(isset($plugins['b']));

  }

  public function testExtensions() {

    $kirby = $this->kirbyInstance();
    $kirby->roots->site = TEST_ROOT_ETC . DS . 'site';

    $kirby->extensions();

    // newly installed kirbytags
    $this->assertTrue(isset(kirbytext::$tags['a']));
    $this->assertTrue(isset(kirbytext::$tags['b']));

  }

  public function testRequest() {

    $kirby = $this->kirbyInstance();
    $this->assertInstanceOf('Kirby\\Request', $kirby->request());

  }

  public function testBranches() {

    // single language
    $kirby = $this->kirbyInstance();

    $this->assertEquals($kirby->roots()->kirby() . DS . 'branches' . DS . 'default.php', $kirby->branch());

    // multi language
    $kirby = $this->kirbyInstance(array(
      'languages' => array(
        array(
          'code'    => 'en',
          'name'    => 'English',
          'locale'  => 'en_US',
          'default' => true
        ),
        array(
          'code'    => 'de',
          'name'    => 'Deutsch',
          'locale'  => 'de_DE',
          'default' => false
        )
      )
    ));

    $this->assertEquals($kirby->roots()->kirby() . DS . 'branches' . DS . 'multilang.php', $kirby->branch());

  }

  public function testRegistry() {
    $this->assertInstanceOf('Kirby\\Registry', $this->kirbyInstance()->registry());
  } 

}
