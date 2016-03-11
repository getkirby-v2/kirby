<?php

require_once('lib/bootstrap.php');

class FilesTest extends KirbyTestCase {

  public function testConstruction() {

    $kirby = $this->kirbyInstance();    
    $site  = $this->siteInstance($kirby);
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

  public function testNot() {

    $kirby = $this->kirbyInstance();    
    $site  = $this->siteInstance($kirby);
    $page  = new Page($site, 'tests/file-extension-case-test');

    // unset by a single filename
    $files = new Files($page);

    $this->assertEquals(2, $files->count());

    $modified = $files->not('a.json');

    $this->assertEquals(1, $modified->count());

    // unset by multiple filenames
    $files = new Files($page);

    $this->assertEquals(2, $files->count());

    $modified = $files->not('a.json', 'b.json');

    $this->assertEquals(0, $modified->count());

    // unset by array
    $files = new Files($page);

    $this->assertEquals(2, $files->count());

    $modified = $files->not(['a.json', 'b.json']);

    $this->assertEquals(0, $modified->count());

    // unset by a collection
    $files = new Files($page);

    $this->assertEquals(2, $files->count());

    $modified = $files->not($files);

    $this->assertEquals(0, $modified->count());

  }

}