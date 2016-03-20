<?php

namespace Kirby\Registry;

class Option extends Entry {

  public function set($key, $value) {
    return $this->kirby->options[$key] = $value;
  }

  public function get($key, $default = null) {
    return $this->kirby->option($key, $default);
  }

}