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

  public function __construct($page, $key, $value = '') {
    $this->page  = $page;
    $this->key   = $key;
    $this->value = $value;
  }

  public function page() {
    return $this->page;
  }
  public function key() {
    return $this->key;
  }
  public function value() {
    return $this->value;
  }
  public function __toString() {
    return $this->value;
  }
  public function toString() {
    return $this->value;
  }
  public function __call($method, $arguments = array()) {
    if(isset(static::$methods[$method])) {
      array_unshift($arguments, clone $this);
      return call(static::$methods[$method], $arguments);
    } else {
      return $this;
    }
  }
}