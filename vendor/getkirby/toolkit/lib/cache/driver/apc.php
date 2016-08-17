<?php

namespace Cache\Driver;

use Cache\Driver;

/**
 * APC Cache
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Apc extends Driver {

  /**
   * Write an item to the cache for a given number of minutes.
   *
   * <code>
   *    // Put an item in the cache for 15 minutes
   *    Cache::set('value', 'my value', 15);
   * </code>
   *
   * @param  string  $key
   * @param  mixed   $value
   * @param  int     $minutes
   * @return void
   */
  public function set($key, $value, $minutes = null) {
    return apc_store($key, $this->value($value, $minutes), $this->expiration($minutes));
  }

  /**
   * Retrieve an item from the cache.
   *
   * @param  string  $key
   * @return mixed
   */
  public function retrieve($key) {
    return apc_fetch($key);
  }

  /**
   * Checks if the current key exists in cache
   *
   * @param string $key
   * @return boolean
   */
  public function exists($key) {
    return apc_exists($key);
  }

  /**
   * Remove an item from the cache
   *
   * @param string $key
   * @return boolean
   */
  public function remove($key) {
    return apc_delete($key);
  }

  /**
   * Flush the entire cache directory
   *
   * @return boolean
   */
  public function flush() {
    return apc_clear_cache('user');
  }

}