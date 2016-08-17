<?php

/**
 * Bitmask
 *
 * Analyzes and sets bitmasks
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Lukas Bestle
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Bitmask {

  /**
   * Checks if a value can be used as bitmask value (checks for a power of two)
   *
   * @param  mixed   $value The value to check for
   * @return boolean
   */
  public static function validValue($value) {
    return is_int($value) && ($value & ($value - 1)) == 0;
  }

  /**
   * Checks if a bitmask includes a value
   *
   * @param  int     $value The value to check for
   * @param  int     $bitmask The bitmask to check in
   * @return boolean
   */
  public static function includes($value, $bitmask) {
    if(!static::validValue($value)) return false;

    return ($bitmask & $value) !== 0;
  }

  /**
   * Adds a value to a bitmask
   *
   * @param  int     $value The value to add
   * @param  int     $bitmask The bitmask to add the value to
   * @return int
   */
  public static function add($value, $bitmask) {
    if(!static::validValue($value)) {
      throw new Exception('The value "' . $value . '" is not appropriate for usage in bitmasks.');
    }

    // check if the bitmask already includes the value
    if(static::includes($value, $bitmask)) return $bitmask;

    return $bitmask | $value;
  }

  /**
   * Removes a value from a bitmask
   *
   * @param  int     $value The value to remove
   * @param  int     $bitmask The bitmask to remove the value from
   * @return int
   */
  public static function remove($value, $bitmask) {
    if(!static::validValue($value)) {
      throw new Exception('The value "' . $value . '" is not appropriate for usage in bitmasks.');
    }

    // check if the bitmask even includes the value
    if(!static::includes($value, $bitmask)) return $bitmask;

    return $bitmask ^ $value;
  }

}