<?php

/**
 * Request
 *
 * Handles all incoming requests
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class R {

  // Stores the raw request data
  protected static $raw = null;

  // Stores all sanitized request data
  protected static $data = null;

  // the request body
  protected static $body = null;

  /**
   * Returns the raw request data
   *
   * @return array
   */
  public static function raw() {
    if(!is_null(static::$raw)) return static::$raw;
    return static::$raw = array_merge($_GET, $_POST);
  }

  /**
   * Returns either the entire data array or parts of it
   *
   * <code>
   *
   * echo r::data('username');
   * // sample output 'bastian'
   *
   * echo r::data('username', 'peter');
   * // if no username is found in the request peter will be echoed
   *
   * </code>
   *
   * @param string $key An optional key to receive only parts of the data array
   * @param mixed $default A default value, which will be returned if nothing can be found for a given key
   * @param mixed
   */
  public static function data($key = null, $default = null) {

    if(is_null(static::$data)) {
      static::$data = static::sanitize(static::raw());

      if(!static::is('GET')) {
        $body = static::body();
        parse_str($body, $parsed);

        if(!is_array($parsed)) {
          $parsed = json_decode($body, false);
          if(!is_array($parsed)) $parsed = array();
        }

        static::$data = array_merge($parsed, static::$data);

      }

    }

    if(is_null($key)) {
      return static::$data;
    } else if(isset(static::$data[$key])) {
      return static::$data[$key];
    } else {
      return $default;
    }

  }

  /**
   * Only returns get data
   *
   * @return array
   */
  public static function getData($key = null, $default = null) {
    return a::get((array)static::sanitize($_GET), $key, $default);
  }

  /**
   * Only returns post data
   *
   * @return array
   */
  public static function postData($key = null, $default = null) {
    return a::get((array)static::sanitize($_POST), $key, $default);
  }

  /**
   * Private method to sanitize incoming request data
   *
   * @param array $data
   * @return array
   */
  protected static function sanitize($data) {

    if(!is_array($data)) {
      return trim(str::stripslashes($data));
    }

    foreach($data as $key => $value) {
      $data[$key] = static::sanitize($value);
    }

    return $data;

  }

  /**
   * Sets or overwrites a variable in the data array
   *
   * <code>
   *
   * r::set('username', 'bastian');
   *
   * dump($request);
   *
   * // sample output: array(
   * //    'username' => 'bastian'
   * //    ... other stuff from the request
   * // );
   *
   * </code>
   *
   * @param mixed $key The key to set/replace. Use an array to set multiple values at once
   * @param mixed $value The value
   * @return array
   */
  public static function set($key, $value = null) {

    // set multiple values at once
    if(is_array($key)) {
      foreach($key as $k => $v) static::set($k, $v);
      // return this for chaining
      return;
    }

    // make sure the data array is actually an array
    if(is_null(static::$data)) static::$data = array();

    // sanitize the
    static::$data[$key] = static::sanitize($value);

    // return the new data array
    return static::$data;

  }

  /**
   * Alternative for r::data($key, $default)
   *
   * <code>
   *
   * echo r::get('username');
   * // sample output 'bastian'
   *
   * echo r::get('username', 'peter');
   * // if no username is found in the request peter will be echoed
   *
   * </code>
   *
   * @param string $key An optional key to receive only parts of the data array
   * @param mixed $default A default value, which will be returned if nothing can be found for a given key
   * @param mixed
   */
  public static function get($key = null, $default = null) {
    return static::data($key, $default);
  }

  /**
   * Removes a variable from the request array
   *
   * @param string $key
   */
  public static function remove($key) {
    unset(static::$data[$key]);
  }

  /**
   * Returns the current request method
   *
   * @return string POST, GET, DELETE, PUT, HEAD, PATCH, etc.
   */
  public static function method() {
    return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : 'GET';
  }

  /**
   * Returns the request body from POST requests for example
   *
   * @return mixed
   */
  public static function body() {
    if(!is_null(static::$body)) return static::$body;
    return static::$body = file_get_contents('php://input');
  }

  /**
   * Returns the files array
   *
   * @param string $key An optional key to receive only parts of the files array
   * @param mixed $default A default value, which will be returned if nothing can be found for a given key
   * @return array
   */
  public static function files($key = null, $default = null) {
    return a::get($_FILES, $key, $default);
  }

  /**
   * Checks if the request is of a specific type:
   *
   * - GET
   * - POST
   * - PUT
   * - PATCH
   * - DELETE
   * - AJAX
   *
   * @return boolean
   */
  public static function is($method) {
    if($method == 'ajax') {
      return static::ajax();
    } else {
      return strtoupper($method) == static::method() ? true : false;
    }
  }

  /**
   * Checks for a specific key in the data array
   *
   * @return boolean
   */
  public static function has($key) {
    $data = static::data();
    return isset($data[$key]);
  }

  /**
   * Returns the referer if available
   *
   * <code>
   *
   * echo r::referer();
   * // sample result: http://someurl.com
   *
   * </code>
   *
   * @param string $default Pass an optional URL to use as default referer if no referer is being found
   * @return string
   */
  public static function referer($default = null) {
    return a::get($_SERVER, 'HTTP_REFERER', $default);
  }

  /**
   * Nobody remembers how to spell it
   * so this is a shortcut
   *
   * <code>
   *
   * echo $request->referrer();
   * // sample result: http://someurl.com
   *
   * </code>
   *
   * @param string $default Pass an optional URL to use as default referer if no referer is being found
   * @return string
   */
  public static function referrer($default = null) {
    return static::referer($default);
  }

  /**
   * Returns the IP address from the
   * request user if available
   *
   * @param mixed
   */
  public static function ip() {
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
  }

  /**
   * Checks if the request has been made from the command line
   *
   * @return boolean
   */
  public static function cli() {
    return defined('STDIN') || (substr(PHP_SAPI, 0, 3) == 'cgi' && $term = getenv('TERM') && $term !== 'unknown');
  }

  /**
   * Checks if the request is an AJAX request
   *
   * <code>
   *
   * if($request->ajax()) echo 'ajax rulez';
   *
   * </code>
   *
   * @return boolean
   */
  public static function ajax() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
  }

  /**
   * Returns the request scheme
   *
   * @return string
   */
  public static function scheme() {
    return url::scheme();
  }

  /**
   * Checks if the request is encrypted
   *
   * @return boolean
   */
  public static function ssl() {
    return static::scheme() == 'https';
  }

  /**
   * Alternative for r::ssl()
   *
   * @return boolean
   */
  public static function secure() {
    return static::ssl();
  }

}