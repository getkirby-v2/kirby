<?php

require_once('lib/bootstrap.php');

class PageTest extends PHPUnit_Framework_TestCase {

  private function kirby() {
    $kirby = new Kirby();    
    $kirby->roots->content = TEST_ROOT_ETC . DS . 'content';
    return $kirby;
  }

  private function site() {
    return new Site($this->kirby());
  }

  public function testConstruction() {

    $kirby = $this->kirby();
    $site  = new Site($kirby);
    $page  = new Page($site, '1-a');

    $this->assertInstanceOf('Kirby', $page->kirby());
    $this->assertEquals($kirby, $page->kirby());
    $this->assertInstanceOf('Site', $page->site());
    $this->assertEquals($site, $page->site());
    $this->assertInstanceOf('Site', $page->parent());
    $this->assertEquals($site, $page->parent());
    $this->assertEquals('1-a', $page->dirname());
    $this->assertEquals(1, $page->depth());
    $this->assertEquals($kirby->roots()->content() . DS . '1-a', $page->root());
    $this->assertEquals('1', $page->num());
    $this->assertEquals('a', $page->uid());
    $this->assertEquals('a', $page->id());
    $this->assertEquals('1-a', $page->diruri());
    $this->assertEquals('/a', $page->url());
    $this->assertTrue($page->isCachable());
    $this->assertEquals('a', $page->slug());
    $this->assertTrue($page->is($page));
    $this->assertTrue($page->equals($page));
    $this->assertFalse($page->isSite());
    $this->assertFalse($page->isActive());
    $this->assertFalse($page->isOpen());
    $this->assertTrue($page->isVisible());
    $this->assertFalse($page->isInvisible());
    $this->assertFalse($page->isHomePage());
    $this->assertFalse($page->isErrorPage());
    $this->assertEquals($page->id(), (string)$page);

  }

  public function testDate() {

    $site = $this->site();
    $page = new Page($site, '1-a');

    $this->assertEquals(1355270400, $page->date());
    $this->assertEquals('2012-12-12', $page->date('Y-m-d'));
    $this->assertEquals('2012-12-12', $page->date('Y-m-d', 'date'));
    $this->assertEquals('2012-12-12', $page->date('Y-m-d', 'created'));

  }

}