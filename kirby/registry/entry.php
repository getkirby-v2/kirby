<?php

namespace Kirby\Registry;

use Kirby;
use Kirby\Registry;

abstract class Entry {

  protected $kirby;
  protected $registry;
  protected $subtype;

  public function __construct(Kirby $kirby, Registry $registry, $subtype = null) {
    $this->kirby    = $kirby;
    $this->registry = $registry;
    $this->subtype  = $subtype;
  }

  public function call($method, $args) {
    return call([$this, $method], $args);
  }

}