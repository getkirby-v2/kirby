<?php

namespace Kirby;

use F;

class Modules {

  public $modules = array();

  public function register($type, $dir) {
    if(isset($this->modules[$type])) {
      $this->modules[$type][] = $dir;
    } else {
      $this->modules[$type] = array($dir);
    }
  }

  public function findFile($type, $file, $extension, $default) {
    $dirs = (array)$this->{$type}();
    array_unshift($dirs, $default);

    foreach($dirs as $dir) {
      $return = $dir . DS . $file . $extension;
      if(f::exists($return)) break;
    }

    return f::exists($return) ? $return : $default . DS . $file . $extension;
  }

  public function __call($method, $arguments) {
    if(isset($this->modules[$method])) {
      return $this->modules[$method];
    }
  }

}
