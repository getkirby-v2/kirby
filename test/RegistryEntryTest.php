<?php

require_once('lib/bootstrap.php');

class RegistryEntryTest extends KirbyTestCase {

  protected function registry() {
    return new Kirby\Registry($this->kirbyInstance());    
  }

  public function testConstruct() {
  
    $kirby    = $this->kirbyInstance();
    $registry = new Kirby\Registry($kirby);
    $entry    = new Kirby\Registry\Entry($registry, 'subtype');

    $this->assertInstanceOf('Kirby', $kirby);
    $this->assertInstanceOf('Kirby\\Registry', $entry->registry());
    $this->assertEquals('subtype', $entry->subtype());

  }  

}