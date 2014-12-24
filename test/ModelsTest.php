<?php

require_once('lib/bootstrap.php');

class ModelsTest extends PHPUnit_Framework_TestCase {

  public function testAutoloading() {

    $kirby = new Kirby(array('debug' => true));
    $kirby->roots->content = TEST_ROOT_ETC . DS . 'content';
    $kirby->roots->site    = TEST_ROOT_ETC . DS . 'site';

    // autoload all models
    $kirby->models();    

    $site = new Site($kirby);
    $a    = $site->find('a');

    $this->assertInstanceOf('Page', $a);
    $this->assertInstanceOf('APage', $a);
    $this->assertEquals('test', $a->customTestMethod());

    $b = $site->find('b');

    $this->assertInstanceOf('Page', $b);
    $this->assertFalse(is_a($b, 'APage'));
    $this->assertFalse('test' == $b->customTestMethod());

  }

}