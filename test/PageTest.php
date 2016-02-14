<?php

require_once('lib/bootstrap.php');

class PageTest extends KirbyTestCase {

  public function testConstruction() {

    $kirby = $this->kirbyInstance();
    $site  = $this->siteInstance($kirby);
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

    $site = $this->siteInstance();
    $page = new Page($site, '1-a');

    $this->assertEquals(1355270400, $page->date());
    $this->assertEquals('2012-12-12', $page->date('Y-m-d'));
    $this->assertEquals('2012-12-12', $page->date('Y-m-d', 'date'));
    $this->assertEquals('2012-12-12', $page->date('Y-m-d', 'created'));

    // switch the date handler
    $kirby = $this->kirbyInstance(array('date.handler' => 'strftime'));
    $site  = $this->siteInstance($kirby);
    $page  = new Page($site, '1-a');

    $this->assertEquals(1355270400, $page->date());
    $this->assertEquals('2012-12-12', $page->date('%Y-%m-%d'));
    $this->assertEquals('2012-12-12', $page->date('%Y-%m-%d', 'date'));
    $this->assertEquals('2012-12-12', $page->date('%Y-%m-%d', 'created'));
  
  }

  public function testModified() {

    $site = $this->siteInstance();
    $page = new Page($site, '1-a');

    $modified = f::modified($page->root());

    $this->assertEquals($modified, $page->modified());
    $this->assertEquals(date('Y-m-d', $modified), $page->modified('Y-m-d'));

    // switch the date handler
    $kirby = $this->kirbyInstance(array('date.handler' => 'strftime'));
    $site  = $this->siteInstance($kirby);
    $page  = new Page($site, '1-a');

    $this->assertEquals($modified, $page->modified());
    $this->assertEquals(strftime('%Y-%m-%d', $modified), $page->modified('%Y-%m-%d'));

  }

  public function testEmptyField() {

    $site = $this->siteInstance();
    $page = new Page($site, '1-a');

    $this->assertInstanceOf('Field', $page->missingfield());
    $this->assertTrue($page->missingfield()->empty());
    $this->assertTrue($page->missingfield()->isEmpty());

  }

  public function testNums() {

    $site  = $this->siteInstance();
    $tests = array(
      '1-a'    => array('1', 'a'),
      'a'      => array(null, 'a'),
      '-a'     => array(null, '-a'),
      '1-1-a'  => array('1', '1-a'),
      '1-1-1'  => array('1', '1-1'),
      '-1-1-1' => array(null, '-1-1-1'),
    );

    foreach($tests as $key => $value) {

      $page = new Page($site, $key);

      $this->assertEquals($value[0], $page->num());
      $this->assertEquals($value[1], $page->uid());

    }

  }

  public function testSort() {
    $site  = $this->siteInstance();
    $page  = new Page($site, '2-b');
    $this->assertEquals('2', $page->num());
    $this->assertEquals($page->num(), $page->sort());
    $this->assertTrue(true, $page->sort(3));
    $this->assertEquals('3', $page->num());
    $this->assertEquals($page->num(), $page->sort());
    $this->assertTrue(true, $page->sort(2));
    $this->assertEquals('2', $page->num());
    $this->assertEquals($page->num(), $page->sort());
  }

}
