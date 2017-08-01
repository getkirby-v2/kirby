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
    '_300' => 'Multiple Choices',
    '_301' => 'Moved Permanently',
    '_302' => 'Found',
    '_303' => 'See Other',
    '_304' => 'Not Modified',
    '_307' => 'Temporary Redirect',
    '_308' => 'Permanent Redirect',

    // client error
    '_400' => 'Bad Request',
    '_401' => 'Unauthorized',
    '_402' => 'Payment Required',
    '_403' => 'Forbidden',
    '_404' => 'Not Found',
    '_405' => 'Method Not Allowed',
    '_406' => 'Not Acceptable',
    '_410' => 'Gone',
    '_418' => 'I\'m a teapot',
    '_451' => 'Unavailable For Legal Reasons',

    // server error
    '_500' => 'Internal Server Error',
    '_501' => 'Not Implemented',
    '_502' => 'Bad Gateway',
    '_503' => 'Service Unavailable',
    '_504' => 'Gateway Time-out'
  );

  /**
   * Sends a content type header
   * 
   * @param string $mime
   * @param string $charset
   * @param boolean $send
   * @return string|null
   */
  public static function contentType($mime, $charset = 'UTF-8', $send = true) {  
    if($found = f::extensionToMime($mime)) $mime = $found;
    $header = 'Content-type: ' . $mime;
    if($charset) $header .= '; charset=' . $charset;
    if(!$send) return $header;
    header($header);
  }


  /**
   * Creates headers by key and value
   * 
   * @param string|array $key
   * @param string|null $value
   * @return string
   */
  public static function create($key, $value = null) {

    if(is_array($key)) {
      $headers = [];
      foreach($key as $k => $v) {
        $headers[] = static::create($k, $v);
      }
      return implode("\r\n", $headers);
    }

    // prevent header injection by stripping any newline characters from single headers
    return str_replace(["\r", "\n"], '', $key . ': ' . $value);
  }

  /**
   * Shortcut for static::contentType()
   * 
   * @param string $mime
   * @param string $charset
   * @param boolean $send
   * @return string|null
   */
  public static function type($mime, $charset = 'UTF-8', $send = true) {
    return static::contentType($mime, $charset, $send);
  }

  /**
   * Sends a status header
   *
   * Checks $code against a list of known status codes. To bypass this check
   * and send a custom status code and message, use a $code string formatted
   * as 3 digits followed by a space and a message, e.g. '999 Custom Status'.
   *
   * @param int|string $code The HTTP status code
   * @param boolean $send If set to false the header will be returned instead
   * @return string|null
   */
  public static function status($code, $send = true) {

    $codes = static::$codes;
    $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

    // allow full control over code and message
    if(is_string($code) && preg_match('/^\d{3} \w.+$/', $code) === 1) {
      $message = substr(rtrim($code), 4);
      $code = substr($code, 0, 3);
    } else {
      $code = !array_key_exists('_' . $code, $codes) ? 500 : $code;
      $message = isset($codes['_' . $code]) ? $codes['_' . $code] : 'Something went wrong';
    }

    $header = $protocol . ' ' . $code . ' ' . $message;
    if(!$send) return $header;

    // try to send the header
    header($header);

  }

  /**
   * Sends a 200 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function success($send = true) {
    return static::status(200, $send);
  }

  /**
   * Sends a 201 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function created($send = true) {
    return static::status(201, $send);
  }

  /**
   * Sends a 202 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function accepted($send = true) {
    return static::status(202, $send);
  }

  /**
   * Sends a 400 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function error($send = true) {
    return static::status(400, $send);
  }

  /**
   * Sends a 403 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function forbidden($send = true) {
    return static::status(403, $send);
  }

  /**
   * Sends a 404 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function notfound($send = true) {
    return static::status(404, $send);
  }

  /**
   * Sends a 404 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function missing($send = true) {
    return static::status(404, $send);
  }

  /**
   * Sends a 410 header
   *
   * @param boolean $send
   * @return string|null
   */
  public static function gone($send = true) {
    return static::status(410, $send);
  }

  /**
   * Sends a 500 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function panic($send = true) {
    return static::status(500, $send);
  }

  /**
   * Sends a 503 header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function unavailable($send = true) {
    return static::status(503, $send);
  }

  /**
   * Sends a redirect header
   * 
   * @param boolean $send
   * @return string|null
   */
  public static function redirect($url, $code = 301, $send = true) {

    $status   = static::status($code, false); 
    $location = 'Location:' . url::unIdn($url);

    if(!$send) {
      return $status . "\r\n" . $location;
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
