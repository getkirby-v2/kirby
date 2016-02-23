<?php

namespace Kirby;

use A;
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

  public function findFile($type, $file, $extensions, $default) {
    $extensions = array_map(function($ext) {
      return ltrim($ext, '.');
    }, (array)$extensions);

    $dirs = a::get($this->modules, $type, array());
    array_unshift($dirs, $default);

    foreach($dirs as $dir) {
      if($return = f::resolve($dir . DS . $file, $extensions)) break;
    }

    return f::exists($return) ? $return : $default . DS . $file . $extensions[0];
  }

  public function __call($method, $arguments) {
    if(isset($this->modules[$method])) {
      return (array)$this->modules[$method];
    }
  }

}
