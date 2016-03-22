<?php

namespace Kirby\Registry;

/**
 * Hook Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Hook extends Entry {

  /**
   * Registers a new hook
   * 
   * This will directly call the $kirby->hook() method
   * A hook has to be a valid closure
   * 
   * @param string $name
   * @param Closure $callback
   * @return Closure
   */
  public function set($name, $callback) {
    return $this->kirby->hook($name, $callback);
  }

}