<?php

/**
 * Obj
 *
 * Obj base class
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Obj extends stdClass {

  /**
   * Constructor
   * 
   * @param array $data
   */
  public function __construct($data = array()) {
    foreach($data as $key => $val) {
      if(!is_string($key) || str::length($key) === 0) continue;
      $this->{$key} = $val;
    }
  }

  /**
   * Magic getter
   * 
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, $arguments) {
    return isset($this->$method) ? $this->$method : null;
  }

  /**
   * Attribute setter 
   * 
   * @param string $key
   * @param mixed $value
   */
  public function set($key, $value) {
    $this->$key = $value;
  }

  /**
   * Attribute getter
   * 
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get($key, $default = null) {
    return isset($this->$key) ? $this->$key : $default;
  }

  /**
   * Converts the object to an array
   * 
   * @return array
   */
  public function toArray() {
    return (array)$this;
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return $this->toArray();
  }

}