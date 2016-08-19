<?php

/**
 * Error Reporting
 *
 * Changes values of the PHP error reporting
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Lukas Bestle
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ErrorReporting {

  /**
   * Returns the current raw value
   *
   * @return int     The current value
   */
  public static function get() {
    return error_reporting();
  }

  /**
   * Sets a new raw error reporting value
   *
   * @param  int     $level The new level to set
   * @return int     The new value
   */
  public static function set($level) {
    if(static::get() !== error_reporting($level)) {
      throw new Exception('Internal error: error_reporting() did not return the old value.');
    }
    return static::get();
  }

  /**
   * Check if the current error reporting includes an error level
   *
   * @param  mixed   $level The level to check for
   * @param  int     $current A custom current level
   * @return boolean
   */
  public static function includes($level, $current = null) {
    // also allow strings
    if(is_string($level)) {
      if(defined($level)) {
        $level = constant($level);
      } else if(defined('E_' . strtoupper($level))) {
        $level = constant('E_' . strtoupper($level));
      } else {
        throw new Exception('The level "' . $level . '" does not exist.');
      }
    }

    $value = ($current)? $current : static::get();
    return bitmask::includes($level, $value);
  }

  /**
   * Adds a level to the current error reporting
   *
   * @param  int     $level The level to add
   * @return boolean
   */
  public static function add($level) {
    // check if it is already added
    if(static::includes($level)) return false;

    $old = static::get();
    $newExpected = bitmask::add($level, $old);
    $newActual = static::set($newExpected);

    return $newActual === $newExpected;
  }

  /**
   * Removes a level from the current error reporting
   *
   * @param  int     $level The level to remove
   * @return boolean
   */
  public static function remove($level) {
    // check if it is already removed
    if(!static::includes($level)) return false;

    $old = static::get();
    $newExpected = bitmask::remove($level, $old);
    $newActual = static::set($newExpected);

    return $newActual === $newExpected;
  }
}
