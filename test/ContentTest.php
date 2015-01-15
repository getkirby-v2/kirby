<?php

require_once('lib/bootstrap.php');

class ContentTest extends KirbyTestCase {

  private function dummyData() {
    return array(
      'title'       => 'Kirby',
      'author'      => 'Bastian Allgeier GmbH',
      'description' => 'This is a Kirby test file',
      'keywords'    => 'Kirby, CMS, file-based',
      'copyright'   => '© 2009-(date: Year) (link: http://getkirby.com text: Kirby)'
    );
  }

  public function testContent() {

    $content = new Content(null, TEST_ROOT_ETC . DS . 'content' . DS . 'site.txt');

    $this->assertEquals(TEST_ROOT_ETC . DS . 'content' . DS . 'site.txt', $content->root);
    $this->assertEquals(TEST_ROOT_ETC . DS . 'content' . DS . 'site.txt', $content->root());

    $this->assertEquals('site', $content->name());

    $this->assertEquals(array_keys($this->dummyData()), $content->fields());
    $this->assertEquals($this->dummyData(), $content->toArray());
    $this->assertEquals($this->dummyData(), $content->data());

    $this->assertTrue($content->exists());

    $this->assertEquals(file_get_contents($content->root()), $content->raw());

    foreach($this->dummyData() as $field => $value) {

      $this->assertEquals($value, $content->$field());
      $this->assertEquals($value, $content->get($field));

      $this->assertInstanceOf('Field', $content->$field());
      $this->assertInstanceOf('Field', $content->get($field));        

    }

  }

  public function testFieldNames() {

    $page    = $this->siteInstance()->page('tests/field-name-test');
    $content = $page->content();
    $fields  = array(
      'camelcase',
      'lowercase',
      'uppercase',
      'name_with_dashes',
      'name_with_underscores',
    );

    foreach($fields as $field) {

      $this->assertEquals('test', $content->get(strtolower($field)));
      $this->assertEquals('test', $content->get(strtoupper($field)));
      $this->assertEquals('test', $page->$field());

    }
    
  }
}