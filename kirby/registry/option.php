<?php

namespace Kirby\Registry;

/**
 * Option Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Option extends Entry {

  /**
   * Sets a Kirby option
   * 
   * This directly adds passed options to the 
   * $kirby->options array and is just a convenient
   * way to do this through the registry
   * 
   * @param string $key
   * @param mixed $value
   * @return mixed
   */
  public function set($key, $value) {
    return $this->kirby->options[$key] = $value;
  }

  /**
   * Retreives an option from the $kirby->$options array
   * 
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public function get($key, $default = null) {
    return $this->kirby->option($key, $default);
  }

}