<?php

require_once('lib/bootstrap.php');

class DimensionsTest extends PHPUnit_Framework_TestCase {

  protected $dim;

  protected function setUp() {

    $this->dim = new Dimensions(500, 300);

  }

  public function testFit() {

    $dim = $this->dim;

    $dim->fit(300);

    $this->assertEquals(300, $dim->width());
    $this->assertEquals(180, $dim->height());

    $dim = $this->dim;

    $dim->fit(1000);

    $this->assertEquals(300, $dim->width());
    $this->assertEquals(180, $dim->height());

    $dim->fit(1000, true);

    $this->assertEquals(1000, $dim->width());
    $this->assertEquals(600, $dim->height());

  }

  public function testFitWidth() {
  
    $dim = $this->dim;

    $dim->fitWidth(300);

    $this->assertEquals(300, $dim->width());
    $this->assertEquals(180, $dim->height());

  }

  public function testFitHeight() {

    $dim = $this->dim;

    $dim->fitHeight(180);

    $this->assertEquals(300, $dim->width());
    $this->assertEquals(180, $dim->height());

  }
  
}
