<?php

namespace Kirby\Registry;

use A;
use Exception;

class Controller extends Entry {

  protected static $controllers = [];

  public function set($name, $callback) {
    
    $name = strtolower($name);

    if($name === 'site') {
      throw new Exception('You are not allowed to set the site controller');
    }

    if(is_a($callback, 'Closure') || file_exists($callback)) {
      return static::$controllers[$name] = $callback;      
    } else {
      throw new Exception('Invalid controller. You must pass a closure or an existing file');
    }

  }

  public function get($name) {

    $name = strtolower($name);    
    $file = $this->kirby->roots()->controllers() . DS . $name . '.php';

    if(file_exists($file)) {
      return include_once $file;
    } 

    if(isset(static::$controllers[$name])) {      
      if(is_a(static::$controllers[$name], 'Closure')) {
        return static::$controllers[$name];  
      } else if(file_exists(static::$controllers[$name])) {
        return include_once static::$controllers[$name];
      }      
    } 

    if(file_exists($this->kirby->roots()->controllers() . DS . 'site.php')) {
      return include_once $this->kirby->roots()->controllers() . DS . 'site.php';            
    }

  }

}