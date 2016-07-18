<?php

namespace Kirby\Registry;

use A;
use Exception;
use F;

/**
 * Blueprint Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Blueprint extends Entry {

  /**
   * Blueprint store
   * 
   * @var array $blueprints
   */
  protected static $blueprints = [];

  /**
   * Adds a new blueprint entry
   * 
   * Pass a path to an existing blueprint file
   * to add it to the registry
   * 
   * @param string/array $name
   * @param string $path
   * @return $path 
   */
  public function set($name, $path) {

    if(is_array($name)) {
      foreach($name as $n) $this->set($n, $path);
      return;
    }

    if(!$this->kirby->option('debug') || file_exists($path)) {    
      return static::$blueprints[$name] = $path;
    } 

    throw new Exception('The blueprint does not exist at the specified path: ' . $path);

  }

  /**
   * Retreives a registered blueprint file path 
   * 
   * @param string $name
   * @return string 
   */
  public function get($name = null) {

    if(is_null($name)) {
      return static::$blueprints;
    }
    
    $file = f::resolve($this->kirby->roots()->blueprints() . DS . str_replace('/', DS, $name), ['php', 'yml', 'yaml']);

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$blueprints, $name);
    }

  }

}