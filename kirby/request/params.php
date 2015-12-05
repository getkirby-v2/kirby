<?php

namespace Kirby\Request;

use Obj;
use Url;

class Params extends Obj {

  public function __toString() {

    $params = array();

    foreach((array)$this as $key => $value) {
      $params[] = $key . url::paramSeparator() . $value;
    }

    return implode('/', $params);

  }

}