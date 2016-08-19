<?php

namespace Cache;

/**
 * Cache Value
 *
 * Stores the value, creation timestamp and expiration timestamp
 * and makes it possible to store all three with a single cache key.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Value {

  // the cached value
  protected $value;

  // the expiration timestamp
  protected $expires;

  // the creation timestamp
  protected $created;

  /**
   * Constructor
   *
   * @param mixed $value
   * @param int $minutes the number of minutes until the value expires
   */
  public function __construct($value, $minutes = null) {

    // keep forever if minutes are not defined
    if(is_null($minutes)) $minutes = 2628000;

    // take the current time
    $time = time();

    $this->value   = $value;
    $this->expires = $time + ($minutes * 60);
    $this->created = $time;

  }

  /**
   * Returns the value
   *
   * @return mixed
   */
  public function value() {
    return $this->value;
  }

  /**
   * Returns the expiration date as UNIX timestamp
   *
   * @return int
   */
  public function expires() {
    return $this->expires;
  }

  /**
   * Returns the creation date as UNIX timestamp
   *
   * @return int
   */
  public function created() {
    return $this->created;
  }

}