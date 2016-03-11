<?php

require_once('lib/bootstrap.php');

class PagesTest extends KirbyTestCase {

  protected function pages() {

    $kirby = $this->kirbyInstance();
    $site  = $this->siteInstance($kirby);
      
    $a    = $site->find('a');
    $b    = $site->find('b');
    $home = $site->find('home');

    $pages = new Pages();
    $pages->add($a);
    $pages->add($b);
    $pages->add($home);

    return $pages;

  }

  public function testNot() {

    // test unsetting by a single uri
    $pages = $this->pages();

    $this->assertEquals(3, $pages->count());

    $pages = $pages->not('a');

    $this->assertEquals(2, $pages->count());

    // test multipler unsetters
    $pages = $this->pages();

    $this->assertEquals(3, $pages->count());

    $pages = $pages->not('a', 'b');

    $this->assertEquals(1, $pages->count());

    // test unsetting by a page object
    $pages = $this->pages();

    $this->assertEquals(3, $pages->count());

    $home = $this->siteInstance()->find('home');

    $pages = $pages->not($home);

    $this->assertEquals(2, $pages->count());

    // test unsetting by a collection

    $pages = $this->pages();

    $this->assertEquals(3, $pages->count());

    $collection = $this->siteInstance()->find('a', 'b');

    $pages = $pages->not($collection);

    $this->assertEquals(1, $pages->count());

  }

}
