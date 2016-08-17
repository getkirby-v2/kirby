<?php

namespace Cache\Driver;

use Cache\Driver;
use A;
use Exception;
use S;

/**
 * Session Cache
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Session extends Driver {

  /**
   * Make sure the session is started within the constructor
   */
  public function __construct() {
    s::start();
    if(!isset($_SESSION['_cache'])) {
      $_SESSION['_cache'] = array();
    }
  }

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
    return $_SESSION['_cache'][$key] = $this->value($value, $minutes);
  }

  /**
   * Retrieve an item from the cache.
   *
   * @param  string  $key
   * @return object CacheValue
   */
  public function retrieve($key) {
    return a::get($_SESSION['_cache'], $key);
  }

  /**
   * Remove an item from the cache
   *
   * @param string $key
   * @return boolean
   */
  public function remove($key) {
    unset($_SESSION['_cache'][$key]);
  }

  /**
   * Flush the entire cache directory
   *
   * @return boolean
   */
  public function flush() {
    $_SESSION['_cache'] = array();
    return true;
  }

}