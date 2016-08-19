<?php

/**
 * Header
 * 
 * Makes sending HTTP headers a breeze
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Header {

  // configuration
  public static $codes = array(
    
    // successful
    '_200' => 'OK', 
    '_201' => 'Created', 
    '_202' => 'Accepted',
    
    // redirection
    '_301' => 'Moved Permanently',
    '_302' => 'Found',
    '_303' => 'See Other',
    '_304' => 'Not Modified',
    '_307' => 'Temporary Redirect', 
    
    // client error
    '_400' => 'Bad Request',
    '_401' => 'Unauthorized',
    '_402' => 'Payment Required',
    '_403' => 'Forbidden',
    '_404' => 'Not Found',
    '_405' => 'Method Not Allowed',
    
    // server error
    '_500' => 'Internal Server Error',
    '_501' => 'Not Implemented',
    '_502' => 'Bad Gateway',
    '_503' => 'Service Unavailable'
  );

  /**
   * Sends a content type header
   * 
   * @param string $mime
   * @param string $charset
   * @param boolean $send
   * @return mixed
   */
  public static function contentType($mime, $charset = 'UTF-8', $send = true) {  
    if(f::extensionToMime($mime)) $mime = f::extensionToMime($mime);
    $header = 'Content-type: ' . $mime;
    if($charset) $header .= '; charset=' . $charset;
    if(!$send) return $header;
    header($header);
  }

  /**
   * Shortcut for static::contentType()
   * 
   * @param string $mime
   * @param string $charset
   * @param boolean $send
   * @return mixed
   */
  public static function type($mime, $charset = 'UTF-8', $send = true) {
    return static::contentType($mime, $charset, $send);
  }

  /**
   * Sends a status header 
   * 
   * @param int $code The HTTP status code
   * @param boolean $send If set to false the header will be returned instead
   * @return mixed
   */
  public static function status($code, $send = true) {

    $codes    = static::$codes;
    $code     = !array_key_exists('_' . $code, $codes) ? 400 : $code;
    $message  = isset($codes['_' . $code]) ? $codes['_' . $code] : 'Something went wrong';
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
    $header   = $protocol . ' ' . $code . ' ' . $message;

    if(!$send) return $header;

    // try to send the header
    header($header);

  }

  /**
   * Sends a 200 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function success($send = true) {
    return static::status(200, $send);
  }

  /**
   * Sends a 201 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function created($send = true) {
    return static::status(201, $send);
  }

  /**
   * Sends a 202 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function accepted($send = true) {
    return static::status(202, $send);
  }

  /**
   * Sends a 400 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function error($send = true) {
    return static::status(400, $send);
  }

  /**
   * Sends a 403 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function forbidden($send = true) {
    return static::status(403, $send);
  }

  /**
   * Sends a 404 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function notfound($send = true) {
    return static::status(404, $send);
  }

  /**
   * Sends a 404 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function missing($send = true) {
    return static::status(404, $send);
  }

  /**
   * Sends a 500 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function panic($send = true) {
    return static::status(500, $send);
  }

  /**
   * Sends a 503 header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function unavailable($send = true) {
    return static::status(503, $send);
  }

  /**
   * Sends a redirect header
   * 
   * @param boolean $send
   * @return mixed
   */
  public static function redirect($url, $code = 301, $send = true) {

    $status   = static::status($code, false); 
    $location = 'Location:' . $url;

    if(!$send) {
      return $status . PHP_EOL . $location;
    }

    header($status);
    header($location);
    exit();

  }

  /**
   * Sends download headers for anything that is downloadable 
   * 
   * @param array $params Check out the defaults array for available parameters
   */
   public static function download($params = array()) {

    $defaults = array(
      'name'     => 'download',
      'size'     => false,
      'mime'     => 'application/force-download',
      'modified' => time()
    );

    $options = array_merge($defaults, $params);

    header('Pragma: public'); 
    header('Expires: 0'); 
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Last-Modified: '. gmdate('D, d M Y H:i:s', $options['modified']) . ' GMT');
    header('Cache-Control: private', false);
    static::contentType($options['mime']);
    header('Content-Disposition: attachment; filename="' . $options['name'] . '"'); 
    header('Content-Transfer-Encoding: binary');
    if($options['size']) header('Content-Length: ' . $options['size']);
    header('Connection: close');

  }

}
