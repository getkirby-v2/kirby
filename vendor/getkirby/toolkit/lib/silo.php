<?php

/**
 * Silo
 *
 * The Silo class is a core class to handle
 * setting, getting and removing static data of
 * a singleton.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Silo {

  public static $data = array();

  public static function set($key, $value = null) {
    if(is_array($key)) {
      return static::$data = array_merge(static::$data, $key);
    } else {
      return static::$data[$key] = $value;
    }
  }

  public static function get($key = null, $default = null) {
    if(empty($key)) return static::$data;
    return isset(static::$data[$key]) ? static::$data[$key] : $default;
  }

  public static function remove($key = null) {
    // reset the entire array
    if(is_null($key)) return static::$data = array();
    // unset a single key
    unset(static::$data[$key]);
    // return the array without the removed key
    return static::$data;
  }

}