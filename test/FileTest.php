<?php

require_once('lib/bootstrap.php');

class FileTest extends PHPUnit_Framework_TestCase {

  public function testConstruction() {

    $kirby = new Kirby();    
    $kirby->roots->content = TEST_ROOT_ETC . DS . 'content';

    $site  = new Site($kirby);
    $page  = new Page($site, '1-a');
    $files = new Files($page);
    $file  = new File($files, 'test.js');

    $this->assertInstanceOf('Kirby', $file->kirby());
    $this->assertEquals($kirby, $file->kirby());
    $this->assertInstanceOf('Site', $file->site());
    $this->assertEquals($site, $file->site());
    $this->assertInstanceOf('Page', $file->page());
    $this->assertEquals($page, $file->page());
    $this->assertInstanceOf('Files', $file->files());
    $this->assertEquals($files, $file->files());
    $this->assertInstanceOf('Media', $file);
    $this->assertEquals($page->root() . DS . 'test.js', $file->root());
    $this->assertEquals($page->contentUrl() . '/test.js', $file->url());
    $this->assertEquals($file->root(), (string)$file);

  }

}