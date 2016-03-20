<?php

namespace Kirby\Registry;

class Component extends Entry {

  public function set($name, $class) {
    return $this->kirby->component($name, $class);
  }

  public function get($name) {
    return $this->kirby->component($name);
  }

}