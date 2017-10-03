<?php

/**
 * Content
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class ContentAbstract {

  public $page   = null;
  public $root   = null;
  public $raw    = null;
  public $data   = array();
  public $fields = array();
  public $name   = null;

  /**
   * Constructor
   * 
   * @param Page $page
   * @param string $root
   */
  public function __construct($page, $root) {

    $this->page = $page;
    $this->root = $root;
    $this->name = pathinfo($root, PATHINFO_FILENAME);

    // stop at invalid files
    if(empty($this->root) or !is_file($this->root) or !is_readable($this->root)) return;

    // read the content file and remove the BOM
    $this->raw = str_replace(BOM, '', file_get_contents($this->root));

    // explode all fields by the line separator
    $fields = preg_split('!\n----\s*\n*!', $this->raw);

    // loop through all fields and add them to the content
    foreach($fields as $field) {
      $pos = strpos($field, ':');
      $key = str_replace(array('-', ' '), '_', strtolower(trim(substr($field, 0, $pos))));

      // Don't add fields with empty keys
      if(empty($key)) continue;

      // add the key to the fields list
      $this->fields[] = $key;

      // add the key object
      $this->data[$key] = new Field($this->page, $key, trim(substr($field, $pos+1)));
    }

  }

  /**
   * Returns the root for the content file
   */
  public function root() {
    return $this->root;
  }

  /**
   * Returns the name of the content file
   * without the extension. This is
   * being used to determine the template for the page
   *
   * @return string
   */
  public function name() {
    return $this->name;
  }

  /**
   * Returns an array with all
   * field names
   *
   * @return array3
   */
  public function fields() {
    return $this->fields;
  }

  /**
   * Returns the raw content from the file
   *
   * @return string
   */
  public function raw() {
    return $this->raw;
  }

  /**
   * Returns the entire data array
   * with all field objects
   *
   * @return array
   */
  public function data() {
    return $this->data;
  }

  /**
   * Checks if the content file exists
   *
   * @return boolean
   */
  public function exists() {
    return file_exists($this->root);
  }

  /**
   * Gets a field from the content
   *
   * @return Field
   */
  public function get($key, $arguments = null) {
  
    // case-insensitive data fetching    
    $key = strtolower($key);

    if(isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      // return an empty field as default
      return new Field($this->page, $key);
    }

  }

  /**
   * Checks if a field exists
   * 
   * @param string $key
   * @return boolean
   */
  public function has($key) {
    return isset($this->data[strtolower($key)]);
  }

  /**
   * Magic getter
   * 
   * @param string $method
   * @param array $arguments Not used
   * @return Field
   */
  public function __call($method, $arguments = null) {
    return $this->get($method, $arguments);
  }

  /**
   * Returns all fields as plain array
   * 
   * @return array
   */
  public function toArray() {
    return array_map(function($item) {
      return $item->value;
    }, $this->data);
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return [
      'root'   => $this->root(),
      'fields' => $this->toArray(),
    ];
  }

  public function __clone() {
    foreach($this->data as $key => $value) {
      $this->data[$key] = clone $value;
    }
  }

}