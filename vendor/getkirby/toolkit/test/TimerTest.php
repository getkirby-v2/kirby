<?php

require_once('lib/bootstrap.php');

class TimerTest extends PHPUnit_Framework_TestCase {

  public function testStart() {

    timer::start();

    // not much to test here
    $this->assertTrue(is_float(timer::stop()));

  }

}
