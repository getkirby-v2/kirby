<?php

namespace Kirby\Registry;

/**
 * Component Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Component extends Entry {

  /**
   * Adds a new core component to Kirby
   * 
   * This will directly call the component method of the 
   * Kirby instance to register the component
   * 
   * @param string $name The name of the component
   * @param string $class A valid component classname. Must be extend the according Kirby component type class
   */
  public function set($name, $class) {
    return $this->kirby->component($name, $class);
  }

  /**
   * Retreives a component from the Kirby component registry
   * 
   * @param string $name
   * @return Kirby\Component
   */
  public function get($name) {
    return $this->kirby->component($name);
  }

}