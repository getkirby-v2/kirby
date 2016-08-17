<?php

require_once('lib/bootstrap.php');

// not really testable on the CLI

class VisitorTest extends PHPUnit_Framework_TestCase {
  
  public function testIp() {
    $this->assertEquals(false, visitor::ip());
  }

  public function testAcceptedLanguage() {
    $this->assertEquals(null, visitor::acceptedLanguage());
  }

}