<?php

/**
 * Visitor
 *
 * Gives some handy information about the current visitor
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Visitor {

  // banned ips
  public static $banned = array();

  // cache for the detected language code
  protected static $acceptedLanguageCode = null;

  /**
   * Returns the ip address of the current visitor
   *
   * @return string
   */
  public static function ip() {
    return getenv('REMOTE_ADDR');
  }

  /**
   * Returns the user agent string of the current visitor
   *
   * @return string
   */
  public static function ua() {
    return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
  }

  /**
   * A more readable but longer alternative for ua()
   *
   * @return string
   */
  public static function userAgent() {
    return static::ua();
  }

  /**
   * Returns the user's accepted language
   *
   * @return string
   */
  public static function acceptedLanguage() {
    return isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
  }

  /**
   * Returns the user's accepted language code
   *
   * @return string
   */
  public static function acceptedLanguageCode() {
    if(!is_null(static::$acceptedLanguageCode)) return static::$acceptedLanguageCode;
    $detected = explode(',', static::acceptedLanguage());
    $detected = explode('-', $detected[0]);
    return static::$acceptedLanguageCode = strtolower($detected[0]);
  }

  /**
   * Returns a number between 0 and 1 that defines how "accepted"
   * a specified MIME type is for the visitor's browser using the
   * HTTP Accept header
   *
   * @param  string $type MIME type like "text/html"
   * @return float        Number between 0 (not accepted) and 1 (very accepted)
   */
  public static function acceptance($type) {
    $accept = a::get($_SERVER, 'HTTP_ACCEPT');

    // if there is no header, everything is accepted
    if(!$accept) return 1;

    // check each type in the Accept header
    foreach(str::split($accept, ',') as $item) {
      $item = str::split($item, ';');
      $mime = a::first($item); // $item now only contains params

      // check if the type matches
      if(!fnmatch($mime, $type, FNM_PATHNAME)) continue;

      // check for the q param ("quality" of the type)
      foreach($item as $param) {
        $param = str::split($param, '=');
        if(a::get($param, 0) === 'q' && $value = a::get($param, 1)) return (float)$value;
      }

      // no quality param, default to a quality of 1
      return 1;
    }

    // no match at all, the type is not accepted
    return 0;
  }

  /**
   * Returns whether a specified MIME type is accepted by the
   * visitor's browser
   *
   * @param  string  $type MIME type like "text/html"
   * @return boolean
   */
  public static function accepts($type) {
    return static::acceptance($type) > 0;
  }

  /**
   * Returns the referrer if available
   *
   * @return string
   */
  public static function referrer() {
    return r::referer();
  }

  /**
   * Nobody can remember if it is written with on or two r
   *
   * @return string
   */
  public static function referer() {
    return r::referer();
  }

  /**
   * Checks if the ip of the current visitor is banned
   *
   * @return boolean
   */
  public static function banned() {
    return in_array(static::ip(), static::$banned);
  }

}