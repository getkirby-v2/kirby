<?php

/**
 * Cache
 *
 * The ultimate cache wrapper for
 * all available drivers
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Cache {

  const ERROR_INVALID_DRIVER = 0;
  const ERROR_INVALID_DRIVER_INSTANCE = 1;
  const ERROR_UNKNOWN_METHOD = 2;

  public static $driver = null;

  /**
   * Setup simplifier for the current driver
   *
   * @param string $driver
   * @param mixed $args
   * @return Cache\Driver
   */
  public static function setup($driver, $args = null) {
    $ref  = new ReflectionClass('Cache\\Driver\\' . $driver);
    return static::$driver = $ref->newInstanceArgs(array($args));
  }

  /**
   * Accessor for all static driver methods
   *
   * @param string $method
   * @param mixed $args
   * @return mixed
   */
  public static function __callStatic($method, $args) {

    if(is_null(static::$driver)) {
      throw new Error('Please define a cache driver', static::ERROR_INVALID_DRIVER);
    }

    if(!is_a(static::$driver, 'Cache\\Driver')) {
      throw new Error('The cache driver must be an instance of the Cache\\Driver class', static::ERROR_INVALID_DRIVER_INSTANCE);
    }

    if(method_exists(static::$driver, $method)) {
      return call(array(static::$driver, $method), $args);
    } else {
      throw new Error('Invalid cache method: ' . $method, static::ERROR_UNKNOWN_METHOD);
    }
  }

}