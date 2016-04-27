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