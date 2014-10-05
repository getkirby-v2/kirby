<?php

namespace Kirby\Request;

use Collection;
use Str;

class Path extends Collection {

  public function __construct($path) {
    parent::__construct(str::split($path, '/'));
  }

  public function __toString() {
    return implode('/', $this->data);
  }

}