<?php

/**
 * Timer
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Timer {

  public static $time = null;

  public static function start() {
    $time = explode(' ', microtime());
    static::$time = (double)$time[1] + (double)$time[0];
  }

  public static function stop() {
    $time  = explode(' ', microtime());
    $time  = (double)$time[1] + (double)$time[0];
    $timer = static::$time;
    return round(($time-$timer), 5);
  }

}