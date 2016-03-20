<?php

namespace Kirby\Registry;

use A;
use Kirbytext;

class Tag extends Entry {

  public function set($name, $data) {  
    kirbytext::$tags[$name] = $data;
  }

  public function get($name) {
    return a::get(kirbytext::$tags, $name);
  }

}