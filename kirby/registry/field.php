<?php

namespace Kirby\Registry;

use A;
use Exception;
use Obj;

class Field extends Entry {

  protected static $fields = [];

  public function set($name, $root) {
    
    $name = strtolower($name);
    $file = $root . DS . $name . '.php';

    if(is_dir($root) && is_file($file)) {
      return static::$fields[$name] = new Obj([
        'root'  => $root,
        'file'  => $file,
        'name'  => $name,
        'class' => $name . 'field',
      ]);
    } 

    throw new Exception('The field does not exist at the specified path: ' . $path);

  }

  public function get($name = null) {

    if(is_null($name)) {
      return static::$fields;
    }

    return a::get(static::$fields, $name);

  }

}