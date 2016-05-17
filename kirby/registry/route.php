<?php

namespace Kirby\Registry;

/**
 * Route Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Route extends Entry {

  /**
   * Registers a new route
   * 
   * This will directly add a route to 
   * Kirby's route system, by calling $kirby->routes()
   * 
   * @param string $attr
   */
  public function set($attr) {
    $this->kirby->routes([$attr]);
  }

}