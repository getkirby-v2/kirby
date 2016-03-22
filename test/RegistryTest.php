<?php

require_once('lib/bootstrap.php');

class RegistryTest extends KirbyTestCase {

  public function testConstruct() {

    $registry = new Kirby\Registry($this->kirbyInstance());

    $this->assertInstanceOf('Kirby', $registry->kirby());
  
  }


}