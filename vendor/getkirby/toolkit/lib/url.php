<?php

/**
 * Url
 *
 * A bunch of handy methods to work with URLs
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Url {

  public static $home    = '/';
  public static $to      = null;
  public static $current = null;

  public static function scheme($url = null) {
    if(is_null($url)) {      
      if(
        (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') ||
        server::get('SERVER_PORT')            == '443' || 
        server::get('HTTP_X_FORWARDED_PORT')  == '443' || 
        server::get('HTTP_X_FORWARDED_PROTO') == 'https' ||
        server::get('HTTP_X_FORWARDED_PROTO') == 'https, http'
      ) {
        return 'https';
      } else {
        return 'http';
      }
    }
    return parse_url($url, PHP_URL_SCHEME);

  }

  /**
   * Returns the current url with all bells and whistles
   *
   * @return string
   */
  public static function current() {
    if(!is_null(static::$current)) return static::$current;
    return static::$current = static::base() . server::get('REQUEST_URI');
  }

  /**
   * Returns the url for the current directory
   *
   * @return string
   */
  public static function currentDir() {
    return dirname(static::current());
  }

  /**
   */
  public static function host($url = null) {
    if(is_null($url)) $url = static::current();
    return parse_url($url, PHP_URL_HOST);
  }

  /**
   * Returns the port for the given url
   *
   * @return mixed
   */
  public static function port($url = null) {
    if(is_null($url)) $url = static::current();
    $port = intval(parse_url($url, PHP_URL_PORT));
    return v::between($port, 1, 65535) ? $port : false;
  }

  /**
   * Returns only the cleaned path of the url
   */
  public static function path($url = null) {

    if(is_null($url)) $url = static::current();

    // if a path is passed, let's pretend this is an absolute url
    // to trick the url parser. It's a bit hacky but it works
    if(!static::isAbsolute($url)) $url = 'http://0.0.0.0/' . $url;

    return ltrim(parse_url($url, PHP_URL_PATH), '/');

  }

  /**
   * Returns the correct separator for parameters
   * depending on the operating system
   * 
   * @return string
   */
  public static function paramSeparator() {
    return detect::windows() ? ';' : ':';
  }

  /**
   * Returns the params in the url
   */
  public static function params($url = null) {
    if(is_null($url)) $url = static::current();
    $path = static::path($url);
    if(empty($path)) return array();
    $params = array();
    foreach(explode('/', $path) as $part) {
      $pos = strpos($part, static::paramSeparator());
      if($pos === false) continue;
      $params[substr($part, 0, $pos)] = urldecode(substr($part, $pos+1));
    }
    return $params;
  }

  /**
   * Returns the path without params
   */
  public static function fragments($url = null) {
    if(is_null($url)) $url = static::current();
    $path = static::path($url);
    if(empty($path)) return null;
    $frag = array();
    foreach(explode('/', $path) as $part) {
      if(strpos($part, static::paramSeparator()) === false) $frag[] = $part;
    }
    return $frag;
  }

  /**
   * Returns the query as array
   */
  public static function query($url = null) {
    if(is_null($url)) $url = static::current();
    parse_str(parse_url($url, PHP_URL_QUERY), $array);
    return $array;
  }

  /**
   * Checks if the url contains a query string
   */
  public static function hasQuery($url = null) {
    if(is_null($url)) $url = static::current();
    return str::contains($url, '?');
  }

  /**
   */
  public static function hash($url = null) {
    if(is_null($url)) $url = static::current();
    return parse_url($url, PHP_URL_FRAGMENT);
  }

  public static function build($parts = array(), $url = null) {

    if(is_null($url)) $url = static::current();

    $defaults = array(
      'scheme'    => static::scheme($url),
      'host'      => static::host($url),
      'port'      => static::port($url),
      'fragments' => static::fragments($url),
      'params'    => static::params($url),
      'query'     => static::query($url),
      'hash'      => static::hash($url),
    );

    $parts  = array_merge($defaults, $parts);
    $result = array(r(!empty($parts['scheme']), $parts['scheme'] . '://') . $parts['host'] . r(!empty($parts['port']), ':' . $parts['port']));

    if(!empty($parts['fragments'])) $result[] = implode('/', $parts['fragments']);
    if(!empty($parts['params']))    $result[] = static::paramsToString($parts['params']);

    // make sure that URLs without any URI end with a slash after the host
    if(count($result) === 1) {
      $result = $result[0] . '/';
    } else {
      $result = implode('/', $result);
    }

    if(!empty($parts['query'])) $result .= '?' . static::queryToString($parts['query']);
    if(!empty($parts['hash']))  $result .= '#' . $parts['hash'];

    return $result;

  }

  public static function queryToString($query = null) {
    if(is_null($query)) $query = url::query();
    return http_build_query($query);
  }

  public static function paramsToString($params = null) {
    if(is_null($params)) $params = url::params();
    $result = array();
    foreach($params as $key => $val) $result[] = $key . static::paramSeparator() . $val;
    return implode('/', $result);
  }

  public static function stripPath($url = null) {
    if(is_null($url)) $url = static::current();
    return static::build(array('fragments' => array(), 'params' => array()), $url);
  }

  public static function stripFragments($url = null) {
    if(is_null($url)) $url = static::current();
    return static::build(array('fragments' => array()), $url);
  }

  public static function stripParams($url = null) {
    if(is_null($url)) $url = static::current();
    return static::build(array('params' => array()), $url);
  }

  /**
   * Strips the query from the URL
   *
   * <code>
   *
   * echo url::stripQuery('http://www.youtube.com/watch?v=9q_aXttJduk');
   * // output: http://www.youtube.com/watch
   *
   * </code>
   *
   * @param  string  $url
   * @return string
   */
  public static function stripQuery($url = null) {
    if(is_null($url)) $url = static::current();
    return static::build(array('query' => array()), $url);
  }

  /**
   * Strips a hash value from the URL
   *
   * <code>
   *
   * echo url::stripHash('http://testurl.com/#somehash');
   * // output: http://testurl.com/
   *
   * </code>
   *
   * @param  string  $url
   * @return string
   */
  public static function stripHash($url) {
    if(is_null($url)) $url = static::current();
    return static::build(array('hash' => ''), $url);
  }

  /**
   * Checks if an URL is absolute
   *
   * @return boolean
   */
  public static function isAbsolute($url) {
    // don't convert absolute urls
    return (str::startsWith($url, 'http://') || str::startsWith($url, 'https://') || str::startsWith($url, '//'));
  }

  /**
   * Convert a relative path into an absolute URL
   *
   * @param string $path
   * @param string $home
   * @return string
   */
  public static function makeAbsolute($path, $home = null) {

    if(static::isAbsolute($path)) return $path;

    // build the full url
    $path = ltrim($path, '/');
    $home = is_null($home) ? static::$home : $home;

    if(empty($path)) return $home;

    return $home == '/' ? '/' . $path : $home . '/' . $path;

  }

  /**
   * Tries to fix a broken url without protocol
   *
   * @param string $url
   * @return string
   */
  public static function fix($url) {
    // make sure to not touch absolute urls
    return (!preg_match('!^(https|http|ftp)\:\/\/!i', $url)) ? 'http://' . $url : $url;
  }

  /**
   * Returns the home url if defined
   *
   * @return string
   */
  public static function home() {
    return static::$home;
  }

  /**
   * The url smart handler. Must be defined before
   *
   * @return string
   */
  public static function to() {
    return call_user_func_array(static::$to, func_get_args());
  }

  /**
   * Return the last url the user has been on if detectable
   *
   * @return string
   */
  public static function last() {
    return r::referer();
  }

  /**
   * Returns the base url
   *
   * @param string $url
   * @return string
   */
  public static function base($url = null) {
    if(is_null($url)) {
      $port = server::get('SERVER_PORT');
      $port = in_array($port, array(80, 443)) ? null : $port;
      return static::scheme() . '://' . server::get('SERVER_NAME', server::get('SERVER_ADDR')) . r($port, ':' . $port);
    } else {
      $port   = static::port($url);
      $scheme = static::scheme($url);
      $host   = static::host($url) . r(is_int($port), ':' . $port);
      return r($scheme, $scheme . '://') . $host;      
    }
  }

  /**
   * Shortens a URL
   * It removes http:// or https:// and uses str::short afterwards
   *
   * <code>
   *
   * echo url::short('http://veryveryverylongurl.com', 30);
   * // output: veryveryverylongurl.com
   *
   * </code>
   *
   * @param  string  $url The URL to be shortened
   * @param  int     $length The final number of characters the URL should have
   * @param  boolean $base True: only take the base of the URL.
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened URL
   */
  public static function short($url, $length = false, $base = false, $rep = '…') {

    if($base) $url = static::base($url);

    // replace all the nasty stuff from the url
    $url = str_replace(array('http://', 'https://', 'ftp://', 'www.'), '', $url);

    // try to remove the last / after the url
    $url = rtrim($url, '/');

    return ($length) ? str::short($url, $length, $rep) : $url;

  }

  /**
   * Tries to convert a URL with an internationalized domain
   * name to the human-readable UTF8 representation
   * Requires the intl PHP extension
   *
   * @param string $url
   * @return string
   */
  public static function idn($url) {

    if(!function_exists('idn_to_utf8')) return $url;

    // disassemble the URL, convert the domain name and reassemble
    $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : INTL_IDNA_VARIANT_2003;
    $host = idn_to_utf8(static::host($url), 0, $variant);
    if($host === false) return $url;
    $url  = static::build(['host' => $host], $url);

    return $url;

  }

  /**
   * Tries to convert a URL with an internationalized domain
   * name to the machine-readable Punycode representation
   *
   * @param string $url
   * @return string
   */
  public static function unIdn($url) {

    if(!function_exists('idn_to_ascii')) return $url;

    // disassemble the URL, convert the domain name and reassemble
    $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : INTL_IDNA_VARIANT_2003;
    $host = idn_to_ascii(static::host($url), 0, $variant);
    if($host === false) return $url;
    $url  = static::build(['host' => $host], $url);

    return $url;
  }

  /**
   * Returns the URL for document root no 
   * matter what the path is. 
   * 
   * @return string
   */
  public static function index() {
    if(r::cli()) {
      return '/';
    } else {
      return static::base() . preg_replace('!\/index\.php$!i', '', server::get('SCRIPT_NAME'));
    }
  }

}

// basic home url setup
url::$home = url::base();

// basic url generator setup
url::$to = function($path = '/') {

  if(url::isAbsolute($path)) return $path;

  $path = ltrim($path, '/');

  if(empty($path)) return url::home();

  return url::home() . '/' . $path;

};
