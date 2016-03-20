<?php

namespace Kirby\Registry;

class Route extends Entry {

  public function set($attr) {
    $this->kirby->routes([$attr]);
  }

}