<?php

namespace Kirby\Registry;

use A;
use Exception;

/**
 * Controller Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Controller extends Entry {

  /**
   * Store of registered controllers
   */
  protected static $controllers = [];
  protected static $cache       = [];

  /**
   * Adds a new controller to the registry
   * 
   * @param string/array $name
   * @param Closure $callback Must be a valid controller callback
   * @return Closure 
   */
  public function set($name, $callback) {

    if(is_array($name)) {
      foreach($name as $n) $this->set($n, $callback);
      return;
    }

    $name = strtolower($name);

    if($name === 'site') {
      throw new Exception('You are not allowed to set the site controller');
    }

    if(!$this->kirby->option('debug') || is_a($callback, 'Closure') || file_exists($callback)) {
      static::$cache = []; // clear cache
      return static::$controllers[$name] = $callback;      
    } else {
      throw new Exception('Invalid controller. You must pass a closure or an existing file');
    }

  }

  /**
   * Retreives a controller from the registry
   * 
   * @param string $name
   * @return Closure
   */
  public function get($name) {

    // get from cache
    if(isset(static::$cache[$name]) && is_a(static::$cache[$name], 'Closure')) {
      return static::$cache[$name];
    }

    // get from main controller directory
    $name = strtolower($name);    
    $file = $this->kirby->roots()->controllers() . DS . $name . '.php';
    if($controller = static::loadFile($name, $file)) return $controller;

    // get from registry
    if(isset(static::$controllers[$name])) {
      if(is_a(static::$controllers[$name], 'Closure')) return static::$cache[$name] = static::$controllers[$name];
      if($controller = static::loadFile($name, static::$controllers[$name])) return $controller;
    }

    // fall back to site controller
    if(isset(static::$cache['site']) && is_a(static::$cache['site'], 'Closure')) {
      return static::$cache['site'];
    }
    if($controller = static::loadFile($name, $this->kirby->roots()->controllers() . DS . 'site.php')) return $controller;

    // no match
    return false;

  }

  /**
   * Loads a controller from file and returns the closure
   *
   * @param string $name
   * @param string $path
   * @return Closure
   */
  protected static function loadFile($name, $path) {

    if(!is_file($path)) return false;

    static::$cache[$name] = require_once($path);
    return static::$cache[$name];

  }

}
