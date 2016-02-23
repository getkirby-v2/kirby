<?php

namespace Kirby;

class Modules {

  public $modules = array();

  public function register($type, $dir) {
    if(isset($this->modules[$type])) {
      $this->modules[$type][] = $dir;
    } else {
      $this->modules[$type] = array($dir);
    }
  }

  public function __call($method, $arguments) {
    if(isset($this->modules[$method])) {
      return $this->modules[$method];
    }
  }

}
