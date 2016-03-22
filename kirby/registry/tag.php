<?php

namespace Kirby\Registry;

use A;
use Kirbytext;

/**
 * Tag Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Tag extends Entry {

  /**
   * Registers a new kirby tag array
   * 
   * This will directly add the tag to the 
   * kirbytext::$tags array.
   * 
   * @param string $name
   * @param array $tag
   */
  public function set($name, $tag) {  
    kirbytext::$tags[$name] = $tag;
  }

  /**
   * Retreives a registered kirby tag
   * 
   * @param string $name
   * @return array
   */
  public function get($name) {
    return a::get(kirbytext::$tags, $name);
  }

}