<?php

namespace Kirby\Registry;

use A;
use Exception;

/**
 * Snippet Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Snippet extends Entry {

  /**
   * List of registered snippet files
   * 
   * @var array $snippets
   */
  protected static $snippets = [];

  /**
   * Registers a new snippet file
   * 
   * You must pass an existing file in order
   * to register it as a valid snippet
   * 
   * @param string $name The name of the snippet. Can contain slashes (i.e. form/field)
   * @param string $path
   * @return string
   */
  public function set($name, $path) {
    
    if(!$this->kirby->option('debug') || file_exists($path)) {    
      return static::$snippets[$name] = $path;
    } 

    throw new Exception('The snippet does not exist at the specified path: ' . $path);

  }

  /**
   * Retrieve the file path for a registered snippet
   * 
   * @param string $name
   * @return string
   */
  public function get($name) {
    
    $file = $this->kirby->component('snippet')->file($name);

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$snippets, $name);
    }

  }

}