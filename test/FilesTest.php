<?php

require_once('lib/bootstrap.php');

class FilesTest extends PHPUnit_Framework_TestCase {

  public function testConstruction() {

    $kirby = new Kirby();    
    $kirby->roots->content = TEST_ROOT_ETC . DS . 'content';

    $site  = new Site($kirby);
    $page  = new Page($site, '1-a');
    $files = new Files($page);

    $this->assertInstanceOf('Kirby', $files->kirby());
    $this->assertEquals($kirby, $files->kirby());
    $this->assertInstanceOf('Site', $files->site());
    $this->assertEquals($site, $files->site());
    $this->assertInstanceOf('Page', $files->page());
    $this->assertEquals($page, $files->page());
    $this->assertEquals(1, $files->count());
    $this->assertInstanceOf('File', $files->find('test.js'));

  }

}