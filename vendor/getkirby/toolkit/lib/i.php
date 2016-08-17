<?php 

/**
 * I
 * 
 * Iterator Base Class
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class I implements Iterator {

  public $data = array();

  /**
   * Constructor
   * 
   * @param array $data
   */
  public function __construct($data = array()) {
    if(is_array($data)) $this->data = $data;
  }

  /** 
   * Checks if the current key is set
   * 
   * `isset($mycollection->mykey)`
   * 
   * @param string $key the key to check
   * @return boolean
   */
  public function __isset($key) {
    return isset($this->data[$key]);
  }

  /** 
   * Removes an element from the array by key
   * 
   * `unset($mycollection->mykey)`
   * 
   * @param string $key the name of the key
   */
  public function __unset($key) {
    unset($this->data[$key]);
  }

  /** 
   * Moves the cusor to the first element of the array
   */
  public function rewind() {
    reset($this->data);
  }

  /** 
   * Returns the current element of the array
   * 
   * @return mixed
   */
  public function current() {
    return current($this->data);
  }

  /** 
   * Returns the current key from the array
   * 
   * @return string
   */
  public function key() {
    return key($this->data);
  }

  /** 
   * Moves the cursor to the previous element in the array
   * and returns it
   * 
   * @return mixed
   */
  public function prev() {
    return prev($this->data);
  }

  /** 
   * Moves the cursor to the next element in the array
   * and returns it
   * 
   * @return mixed
   */
  public function next() {
    return next($this->data);
  }

  /** 
   * Checks if an element is valid
   * This is needed for the Iterator implementation
   * 
   * @return boolean
   */
  public function valid() {
    return $this->current() !== false;
  }

}