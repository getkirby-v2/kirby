<?php

namespace Kirby\Request;

use Obj;
use Url;

class Params extends Obj {

  public function __toString() {
    return url::paramsToString($this->toArray());
  }

}