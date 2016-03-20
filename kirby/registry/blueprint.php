<?php

namespace Kirby\Registry;

use A;
use Exception;
use F;

class Blueprint extends Entry {

  protected static $blueprints = [];

  public function set($name, $path) {
    
    if(file_exists($path)) {
      return static::$blueprints[$name] = $path;
    } 

    throw new Exception('The blueprint does not exist at the specified path: ' . $path);

  }

  public function get($name) {
    
    $file = f::resolve($this->kirby->roots()->blueprints() . DS . str_replace('/', DS, $name), ['php', 'yml', 'yaml']);

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$blueprints, $name);
    }

  }

}