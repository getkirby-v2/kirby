<?php

namespace Kirby\Registry;

use A;
use Exception;

class Widget extends Entry {

  protected static $widgets = [];

  public function set($name, $path) {
    
    if(is_dir($path)) {
      return static::$widgets[$name] = $path;
    } 

    throw new Exception('The widget does not exist at the specified path: ' . $path);

  }

  public function get($name = null) {

    if(is_null($name)) {
      return static::$widgets;
    }

    $file = $this->kirby->roots()->widgets() . DS . str_replace('/', DS, $name) . '.php';

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$widgets, $name);
    }

  }

}