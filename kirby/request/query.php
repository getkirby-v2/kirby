<?php

namespace Kirby\Request;

use Obj;

class Query extends Obj {

  public function __toString() {
    return http_build_query((array)$this);
  }

}