<?php

namespace Kirby\Registry;

use A;
use Exception;
use Obj;

/**
 * Field Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Field extends Entry {

  /**
   * Store for registered fields
   *
   * @var array $fields
   */
  protected static $fields = [];

  /**
   * Adds a new field to the registry
   * 
   * @param string $name
   * @param string $root valid field directory path
   * @param boolean $automatic Whether the field is registered automatically (true) or by a plugin (false)
   * @return Obj generic Kirby object with info about the field
   */
  public function set($name, $root, $automatic = false) {
    
    $name = strtolower($name);
    $file = $root . DS . $name . '.php';

    if(!$this->kirby->option('debug') || (is_dir($root) && is_file($file))) {
      return static::$fields[$name] = new Obj([
        'root'  => $root,
        'file'  => $file,
        'name'  => $name,
        'class' => $name . 'field',
      ]);
    } 

    if(!$automatic) throw new Exception('The field does not exist at the specified path: ' . $root);

  }

  /**
   * Retreives a field info object from the registry
   * 
   * @param string|null $name If null, all registered fields will be returned as array
   * @param Obj|null|array
   */
  public function get($name = null) {

    if(is_null($name)) {
      return static::$fields;
    }

    return a::get(static::$fields, $name);

  }

}
