<?php

/**
 * Field
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class FieldAbstract {

  static public $methods = array();

  public $page;
  public $key;
  public $value;

  /**
   * Constructor
   * 
   * @param Page $page
   * @param string $key
   * @param string $value
   */
  public function __construct($page, $key, $value = '') {
    $this->page  = $page;
    $this->key   = $key;
    $this->value = $value;
  }

  /**
   * Returns the parent page object
   * 
   * @return Page
   */
  public function page() {
    return $this->page;
  }

  /**
   * Checks if the field actually exists
   * in the page's content file
   * 
   * @return boolean
   */
  public function exists() {
    return $this->page->content()->has($this->key);
  }

  /**
   * Returns the field's key name
   * 
   * @return string
   */
  public function key() {
    return $this->key;
  }

  /**
   * Returns the field value
   * 
   * @return string
   */
  public function value() {
    return $this->value;
  }

  /**
   * Checks if the field is translated
   * Only applicable for multilang sites
   * 
   * @param string $lang
   * @return boolean
   */
  public function isTranslated($lang = null) {
    return true;
  }

  /**
   * Makes it possible to convert the 
   * object to a string
   * 
   * @return string
   */
  public function __toString() {
    return (string)$this->value;
  }

  /**
   * Returns the field value
   * 
   * @return string
   */
  public function toString() {
    return $this->value;
  }

  /**
   * Applies registered Field methods 
   * 
   * @param string $method
   * @param array $arguments
   * @return mixed
   */
  public function __call($method, $arguments = array()) {
    if(isset(static::$methods[$method])) {
      array_unshift($arguments, clone $this);
      return call(static::$methods[$method], $arguments);
    } else {
      return $this;
    }
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return [
      'page'    => $this->page()->id(),
      'key'     => $this->key(),
      'value'   => $this->value()
    ];
  }

}