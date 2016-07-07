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
   * 
   * @var array $controllers
   */
  protected static $controllers = [];

  /**
   * Adds a new controller to the registry
   * 
   * @param string $name
   * @param Closure $callback Must be a valid controller callback
   * @return Closure 
   */
  public function set($name, $callback) {
    
    $name = strtolower($name);

    if($name === 'site') {
      throw new Exception('You are not allowed to set the site controller');
    }

    if(!$this->kirby->option('debug') || is_a($callback, 'Closure') || file_exists($callback)) {
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
    if(isset(static::$controllers[$name]) && is_a(static::$controllers[$name], 'Closure')) {
      return static::$controllers[$name];
    }

    // get from main controller directory
    $name = strtolower($name);    
    $file = $this->kirby->roots()->controllers() . DS . $name . '.php';
    if($controller = static::loadFile($name, $file)) return $controller;

    // get from registry
    if(isset(static::$controllers[$name]) && $controller = static::loadFile($name, static::$controllers[$name])) return $controller;

    // fall back to site controller
    if(isset(static::$controllers['site']) && is_a(static::$controllers['site'], 'Closure')) {
      return static::$controllers['site'];
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

    static::$controllers[$name] = require_once($path);
    return static::$controllers[$name];

  }

}
