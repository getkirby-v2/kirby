<?php

namespace Kirby\Registry;

class Hook extends Entry {

  public function set($name, $callback) {
    return $this->kirby->hook($name, $callback);
  }

}