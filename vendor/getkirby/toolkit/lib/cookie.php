<?php

/**
 * Cookie
 * 
 * This class makes cookie handling easy
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Cookie {

  // configuration
  public static $salt = 'KirbyToolkitCookieSalt';

  /**
   * Set a new cookie
   * 
   * <code>
   * 
   * cookie::set('mycookie', 'hello', 60);
   * // expires in 1 hour
   * 
   * </code>
   * 
   * @param  string  $key The name of the cookie
   * @param  string  $value The cookie content
   * @param  int     $lifetime The number of minutes until the cookie expires
   * @param  string  $path The path on the server to set the cookie for
   * @param  string  $domain the domain 
   * @param  boolean $secure only sets the cookie over https
   * @param  boolean $httpOnly avoids the cookie to be accessed via javascript
   * @return boolean true: the cookie has been created, false: cookie creation failed
   */
  public static function set($key, $value, $lifetime = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true) {
      
    // convert array values to json 
    if(is_array($value)) $value = a::json($value);

    // hash the value
    $value = static::hash($value) . '+' . $value;

    // store that thing in the cookie global 
    $_COOKIE[$key] = $value;
    
    // store the cookie
    return setcookie($key, $value, static::lifetime($lifetime), $path, $domain, $secure, $httpOnly);
  
  }

  /**
   * Calculates the lifetime for a cookie
   * 
   * @return int
   */
  public static function lifetime($minutes) {
    return $minutes > 0 ? (time() + ($minutes * 60)) : 0;
  }

  /**
   * Stores a cookie forever
   * 
   * <code>
   * 
   * cookie::forever('mycookie', 'hello');
   * // never expires
   * 
   * </code>
   * 
   * @param  string  $key The name of the cookie
   * @param  string  $value The cookie content
   * @param  string  $path The path on the server to set the cookie for
   * @param  string  $domain the domain 
   * @param  boolean $secure only sets the cookie over https
   * @return boolean true: the cookie has been created, false: cookie creation failed
   */
  public static function forever($key, $value, $path = '/', $domain = null, $secure = false) {
    return static::set($key, $value, 2628000, $path, $domain, $secure);
  }

  /**
   * Get a cookie value
   * 
   * <code>
   * 
   * cookie::get('mycookie', 'peter');
   * // sample output: 'hello' or if the cookie is not set 'peter'
   * 
   * </code>
   * 
   * @param  string  $key The name of the cookie
   * @param  string  $default The default value, which should be returned if the cookie has not been found
   * @return mixed   The found value
   */
  public static function get($key = null, $default = null) {
    if(is_null($key)) return $_COOKIE;
    $value = isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    return empty($value) ? $default : static::parse($value);
  }

  /**
   * Checks if a cookie exists
   * 
   * @return boolean
   */
  public static function exists($key) {
    return !is_null(static::get($key));
  }

  /**
   * Creates a hash for the cookie value
   * salted with the secret cookie salt string from the defaults
   * 
   * @param string $value
   * @return string
   */
  protected static function hash($value) {
    return sha1($value . static::$salt);
  }

  /**
   * Parses the hashed value from a cookie
   * and tries to extract the value 
   * 
   * @param string $string
   * @return mixed
   */
  protected static function parse($string) {

    // extract hash and value
    $parts = str::split($string, '+');
    $hash  = a::first($parts);
    $value = a::last($parts);

    // if the hash or the value is missing at all return null
    if(empty($hash) || empty($value)) return null;

    // compare the extracted hash with the hashed value
    if($hash !== static::hash($value)) return null;

    return $value;

  }

  /**
   * Remove a cookie
   * 
   * <code>
   * 
   * cookie::remove('mycookie');
   * // mycookie is now gone
   * 
   * </code>
   * 
   * @param  string  $key The name of the cookie
   * @return mixed   true: the cookie has been removed, false: the cookie could not be removed
   */
  public static function remove($key) {
    if(isset($_COOKIE[$key])) {
      unset($_COOKIE[$key]);
      return setcookie($key, '', time() - 3600, '/');      
    }
  }

}