<?php

namespace Kirby;

use Kirby;

class Component {

  protected $kirby;

  public function __construct(Kirby $kirby) {
    $this->kirby = $kirby;
  }

  public function kirby() {
    return $this->kirby;
  }

}