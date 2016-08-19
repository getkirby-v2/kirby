<?php

namespace Cache\Driver;

use Cache\Driver;

/**
 * Memcache
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Memcached extends Driver {

  // store for the memache connection
  protected $connection = null;

  /**
   * Set all parameters which are needed for the memcache client
   * see defaults for available parameters
   *
   * @param array $params
   */
  public function __construct($params = array()) {

    $defaults = array(
      'host'    => 'localhost',
      'port'    => 11211, 
      'prefix'  => null,
    );

    $this->options    = array_merge($defaults, (array)$params);
    $this->connection = new \Memcached();
    $this->connection->addServer($this->options['host'], $this->options['port']);

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
    return $this->connection->set($this->key($key), $this->value($value, $minutes), $this->expiration($minutes));
  }

  /**
   * Returns the full keyname 
   * including the prefix (if set)
   * 
   * @param string $key
   * @return string 
   */
  public function key($key) {
    return $this->options['prefix'] . $key;
  }

  /**
   * Retrieve the CacheValue object from the cache.
   *
   * @param  string  $key
   * @return object CacheValue
   */
  public function retrieve($key) {
    return $this->connection->get($this->key($key));
  }

  /**
   * Remove an item from the cache
   *
   * @param string $key
   * @return boolean
   */
  public function remove($key) {
    return $this->connection->delete($this->key($key));
  }

  /**
   * Checks when an item in the cache expires
   *
   * @param string $key
   * @return int
   */
  public function expires($key) {
    return parent::expires($this->key($key));
  }

  /**
   * Checks if an item in the cache is expired
   *
   * @param string $key
   * @return int
   */
  public function expired($key) {
    return parent::expired($this->key($key));
  }

  /**
   * Checks when the cache has been created
   *
   * @param string $key
   * @return int
   */
  public function created($key) {
    return parent::created($this->key($key));
  }

  /**
   * Flush the entire cache directory
   *
   * @return boolean
   */
  public function flush() {
    return $this->connection->flush();
  }

}