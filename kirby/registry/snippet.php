<?php

namespace Kirby\Registry;

use A;
use Exception;

class Snippet extends Entry {

  protected static $snippets = [];

  public function set($name, $path) {
    
    if(file_exists($path)) {
      return static::$snippets[$name] = $path;
    } 

    throw new Exception('The snippet does not exist at the specified path: ' . $path);

  }

  public function get($name) {
    
    $file = $this->kirby->roots()->snippets() . DS . str_replace('/', DS, $name) . '.php';

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$snippets, $name);
    }

  }

}