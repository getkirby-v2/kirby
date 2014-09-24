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
abstract class FieldAbstract extends stdClass {

  static public $methods = array();

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