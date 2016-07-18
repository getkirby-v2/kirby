<?php

namespace Kirby\Registry;

use A;
use Exception;

/**
 * Template Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Template extends Entry {

  /**
   * List of registered template files
   * 
   * @var array $templates
   */
  protected static $templates = [];

  /**
   * Registers a new template file
   * 
   * Must be an existing file
   * 
   * @param string/array $name
   * @param string $path
   */
  public function set($name, $path) {

    if(is_array($name)) {
      foreach($name as $n) $this->set($n, $path);
      return;
    }

    if(!$this->kirby->option('debug') || file_exists($path)) {    
      return static::$templates[$name] = $path;
    } 

    throw new Exception('The template does not exist at the specified path: ' . $path);

  }

  /**
   * Retrieves a registered template file
   * 
   * @param string $name
   * @return string
   */
  public function get($name) {
    
    $file = $this->kirby->component('template')->file($name);

    if(file_exists($file)) {
      return $file;
    } else {
      return a::get(static::$templates, $name);
    }

  }

}