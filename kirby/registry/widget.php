<?php

namespace Kirby\Registry;

use A;
use Exception;

/**
 * Widget Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Widget extends Entry {

  /**
   * List of registered widget directories
   * 
   * @var array $widgets
   */
  protected static $widgets = [];

  /**
   * Registers a new widget 
   * 
   * You must pass an existing widget directory
   * 
   * @param string $name
   * @param string $path
   * @return string
   */
  public function set($name, $path) {
    
    if(!$this->kirby->option('debug') || is_dir($path)) {    
      return static::$widgets[$name] = $path;
    } 

    throw new Exception('The widget does not exist at the specified path: ' . $path);

  }

  /**
   * Retreives a registered widget directory
   * 
   * @param string|null $name If null, all registered widgets will be returned as array
   * @return string|array
   */
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