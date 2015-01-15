<?php

require_once('lib/bootstrap.php');

class KirbytextTest extends KirbyTestCase {

  public function kt($value, $markdownExtra = false) {

    $kirby = $this->kirbyInstance(array(
      'markdown.extra' => $markdownExtra
    ));    
      
    $site  = $this->siteInstance($kirby);
    $page  = new Page($site, '1-a');
    $field = new Field($page, null, $value);

    return new Kirbytext($field);

  }

  public function runTests($result) {

    $root = TEST_ROOT_ETC . DS . 'kirbytext';
    $dirs = dir::read($root);

    foreach($dirs as $dir) {

      $testFile     = $root . DS . $dir . DS . 'test.txt';
      $expectedFile = $root . DS . $dir . DS . 'expected.html';

      $this->assertEquals(f::read($expectedFile), $result(f::read($testFile)), 'test: ' . $dir);

    }

  }

  public function testWithMarkdown() {

    $self = $this;
    $this->runTests(function($value) use($self) {
      return (string)$self->kt($value);
    });

  }

  public function testWithMarkdownExtra() {

    $self = $this;
    $this->runTests(function($value) use($self) {
      return (string)$self->kt($value, true);
    });

  }

}