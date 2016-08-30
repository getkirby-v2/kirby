<?php

namespace Kirby\Registry;

use Dir;
use Exception;
use F;

/**
 * Role Registy Entry
 *
 * @package   Kirby CMS
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Role extends Entry {

  /**
   * Store of registered roles
   */
  protected static $roles = [];
  protected static $cache = [];

  /**
   * Adds a new role to the registry
   * 
   * @param string/array $name
   * @param mixed $role Must be a valid role array or path to a PHP file
   * @return Closure 
   */
  public function set($name, $role) {

    if(is_array($name)) {
      foreach($name as $n) $this->set($n, $role);
      return;
    }

    $name = strtolower($name);

    if(!$this->kirby->option('debug') || is_array($role) || file_exists($role)) {
      static::$cache = []; // clear cache
      return static::$roles[$name] = $role;
    } else {
      throw new Exception('Invalid role. You must pass an array or an existing file');
    }

  }

  /**
   * Retreives a role from the registry
   * If called without params, retrieves a list of role names
   * 
   * @param string $name
   * @return array
   */
  public function get($name = null) {

    // retrieve all role names if no name is given
    if(!$name) {
      $roles = [];
      foreach(dir::read($this->kirby->roots()->roles()) as $role) {
        if(f::extension($role) === 'php') $roles[] = f::name($role);
      }
      return array_unique(array_merge($roles, array_keys(static::$roles)));
    }

    // get from cache
    if(isset(static::$cache[$name]) && is_array(static::$cache[$name])) {
      return static::$cache[$name];
    }

    // get from main role directory
    $name = strtolower($name);    
    $file = $this->kirby->roots()->roles() . DS . $name . '.php';
    if(is_array($role = $this->loadFile($name, $file))) return $role;

    // get from registry
    if(isset(static::$roles[$name])) {
      if(is_array(static::$roles[$name])) return static::$cache[$name] = static::$roles[$name];
      if(is_array($role = $this->loadFile($name, static::$roles[$name]))) return $role;
    }

    // no match
    return false;

  }

  /**
   * Loads a role from file and returns the array
   * 
   * This method can't be static because of an issue
   * with closure binding in PHP < 7
   * ("Cannot bind an instance to a static closure")
   * See https://bugs.php.net/bug.php?id=68792
   *
   * @param string $name
   * @param string $path
   * @return array
   */
  protected function loadFile($name, $path) {

    if(!is_file($path)) return false;

    static::$cache[$name] = require_once($path);
    return static::$cache[$name];

  }

}
