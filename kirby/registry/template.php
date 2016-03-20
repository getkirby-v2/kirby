<?php

namespace Kirby\Registry;

use A;
use Exception;

class Template extends Entry {

  protected static $templates = [];

  public function set($name, $path) {
    
    if(file_exists($path)) {
      return static::$templates[$name] = $path;
    } 

    throw new Exception('The template does not exist at the specified path: ' . $path);

  }

  public function get($name) {
    
    $file = $this->kirby->roots()->templates() . DS . str_replace('/', DS, $name) . '.php';

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$templates, $name);
    }

  }

}