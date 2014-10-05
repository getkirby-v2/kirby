<?php

namespace Kirby\Request;

use Obj;

class Params extends Obj {

  public function __toString() {

    $params = array();

    foreach((array)$this as $key => $value) {
      $params[] = $key . ':' . $value;
    }

    return implode('/', $params);

  }

}