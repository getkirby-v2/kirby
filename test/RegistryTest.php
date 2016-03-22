<?php

require_once('lib/bootstrap.php');

class RegistryTest extends KirbyTestCase {

  public function testConstruct() {

    $registry = new Kirby\Registry($this->kirbyInstance());

    $this->assertInstanceOf('Kirby', $registry->kirby());
  
  }  

  public function testEntry() {

    $registry = new Kirby\Registry($this->kirbyInstance());

    $types = [
      'blueprint',
      'component',
      'controller',
      'entry',
      'field',
      'hook',
      'option',
      'route',
      'snippet',
      'tag',
      'template',
      'widget',
    ];

    foreach($types as $type) {
      $this->assertInstanceOf('Kirby\\Registry\\' . $type, $registry->entry($type));
    }

    // methods with subtypes
    $this->assertInstanceOf('Kirby\\Registry\\Method', $registry->entry('page::method'));
    $this->assertInstanceOf('Kirby\\Registry\\Method', $registry->entry('pages::method'));
    $this->assertInstanceOf('Kirby\\Registry\\Method', $registry->entry('file::method'));
    $this->assertInstanceOf('Kirby\\Registry\\Method', $registry->entry('files::method'));

    // models with subtypes
    $this->assertInstanceOf('Kirby\\Registry\\Model', $registry->entry('page::model'));

  }

}