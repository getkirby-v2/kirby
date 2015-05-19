<?php

require_once('lib/bootstrap.php');

class FileTest extends KirbyTestCase {

  public function testConstruction() {

    $kirby = $this->kirbyInstance();
    $site  = $this->siteInstance($kirby);
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

  public function testModified() {

    $page = $this->siteInstance()->page('tests/file-extension-case-test');
    $file = $page->file('a.json');

    $modified = f::modified($file->root());

    $this->assertEquals($modified, $file->modified());
    $this->assertEquals(date('Y-m-d', $modified), $file->modified('Y-m-d'));

    // switch the date handler
    $kirby = $this->kirbyInstance(array('date.handler' => 'strftime'));
    $site  = $this->siteInstance($kirby);
    $page  = $site->page('tests/file-extension-case-test');
    $file  = $page->file('a.json');

    $this->assertEquals($modified, $file->modified());
    $this->assertEquals(strftime('%Y-%m-%d', $modified), $file->modified('%Y-%m-%d'));

  }

  /**
   * Test if files are being found regardles of 
   * the extension format (upper|lowercase)
   */
  public function testFileExtensionCase() {

    $page = $this->siteInstance()->page('tests/file-extension-case-test');

    $a1 = $page->file('a.JSON');
    $a2 = $page->file('a.json');
    $b1 = $page->file('b.json');
    $b2 = $page->file('b.JSON');

    $this->assertEquals('a.JSON', $a1->filename());
    $this->assertEquals('a.JSON', $a2->filename());
    $this->assertEquals('b.json', $b1->filename());
    $this->assertEquals('b.json', $b2->filename());

  }

}