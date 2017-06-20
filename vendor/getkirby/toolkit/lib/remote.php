<?php

/**
 * Remote
 *
 * A handy little class to handle
 * all kind of remote requests
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Remote {

  // configuration
  public static $defaults = array(
    'method'   => 'GET',
    'data'     => array(),
    'file'     => null,
    'timeout'  => 10,
    'headers'  => array(),
    'encoding' => 'utf-8',
    'agent'    => null,
    'body'     => true,
    'progress' => null,
  );

  // store for the response object
  protected $response = null;

  // all options for the request
  protected $options = array();

  // all received headers
  protected $headers = array();

  /**
   * Constructor
   *
   * @param string $url
   * @param array $options
   */
  public function __construct($url, $options = array()) {

    // set all options
    $this->options = array_merge(static::$defaults, $options);

    // add the url
    $this->options['url'] = $url;

    // send the request
    $this->send();

  }

  /**
   * Sets up all curl options and sends the request
   *
   * @return object Response
   */
  protected function send() {

    // start a curl request
    $curl = curl_init();

    // curl options
    $params = array(
      CURLOPT_URL              => $this->options['url'],
      CURLOPT_ENCODING         => $this->options['encoding'],
      CURLOPT_CONNECTTIMEOUT   => $this->options['timeout'],
      CURLOPT_TIMEOUT          => $this->options['timeout'],
      CURLOPT_AUTOREFERER      => true,
      CURLOPT_RETURNTRANSFER   => $this->options['body'],
      CURLOPT_FOLLOWLOCATION   => true,
      CURLOPT_MAXREDIRS        => 10,
      CURLOPT_SSL_VERIFYPEER   => false,
      CURLOPT_HEADER           => false,
      CURLOPT_HEADERFUNCTION   => array($this, 'header')
    );

    // add the progress 
    if(is_callable($this->options['progress'])) {
      $params[CURLOPT_NOPROGRESS]       = false;
      $params[CURLOPT_PROGRESSFUNCTION] = $this->options['progress'];
    }

    // add all headers
    if(!empty($this->options['headers'])) $params[CURLOPT_HTTPHEADER] = $this->options['headers'];

    // add the user agent
    if(!empty($this->options['agent'])) $params[CURLOPT_USERAGENT] = $this->options['agent'];

    // do some request specific stuff
    switch(strtolower($this->options['method'])) {
      case 'post':
        $params[CURLOPT_POST]       = true;
        $params[CURLOPT_POSTFIELDS] = $this->postfields($this->options['data']);
        break;
      case 'put':

        $params[CURLOPT_CUSTOMREQUEST] = 'PUT';
        $params[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);

        // put a file
        if($this->options['file']) {
          $params[CURLOPT_INFILE]     = fopen($this->options['file'], 'r');
          $params[CURLOPT_INFILESIZE] = f::size($this->options['file']);
        }

        break;
      case 'patch':
        $params[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        $params[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
        break;
      case 'delete':
        $params[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        $params[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
        break;
      case 'head':
        $params[CURLOPT_CUSTOMREQUEST] = 'HEAD';
        $params[CURLOPT_POSTFIELDS]    = $this->postfields($this->options['data']);
        $params[CURLOPT_NOBODY]        = true;
        break;
    }

    curl_setopt_array($curl, $params);

    $content  = curl_exec($curl);
    $error    = curl_errno($curl);
    $message  = curl_error($curl);
    $info     = curl_getinfo($curl);

    curl_close($curl);

    $this->response = new RemoteResponse();
    $this->response->headers = $this->headers;
    $this->response->error   = $error;
    $this->response->message = $message;
    $this->response->content = $content;
    $this->response->code    = $info['http_code'];
    $this->response->info    = $info;

    return $this->response;

  }

  /**
   * Used by curl to parse incoming headers
   *
   * @param object $curl the curl connection
   * @param string $header the header line
   * @return int the length of the heade
   */
  protected function header($curl, $header) {

    $parts = str::split($header, ':');

    if(!empty($parts[0]) && !empty($parts[1])) {
      $key = array_shift($parts);
      $this->headers[$key] = implode(':', $parts);
    }

    return strlen($header);

  }

  /**
   * Returns all options which have been
   * set for the current request
   *
   * @return array
   */
  public function options() {
    return $this->options;
  }

  /**
   * Returns the response object for
   * the current request
   *
   * @return object Response
   */
  public function response() {
    return $this->response;
  }

  /**
   * Static method to init this class and send a request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function request($url, $params = array()) {
    $request = new self($url, $params);
    return $request->response();
  }

  /**
   * Static method to send a GET request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function get($url, $params = array()) {

    $defaults = array(
      'method' => 'GET',
      'data'   => array(),
    );

    $options = array_merge($defaults, $params);
    $query   = http_build_query($options['data']);

    if(!empty($query)) {
      $url = (url::hasQuery($url)) ? $url . '&' . $query : $url . '?' . $query;
    }

    // remove the data array from the options
    unset($options['data']);

    $request = new self($url, $options);
    return $request->response();

  }

  /**
   * Static method to send a POST request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function post($url, $params = array()) {

    $defaults = array(
      'method' => 'POST'
    );

    $request = new self($url, array_merge($defaults, $params));
    return $request->response();

  }

  /**
   * Static method to send a PUT request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function put($url, $params = array()) {

    $defaults = array(
      'method' => 'PUT'
    );

    $request = new self($url, array_merge($defaults, $params));
    return $request->response();

  }

  /**
   * Static method to send a PATCH request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function patch($url, $params = array()) {

    $defaults = array(
      'method' => 'PATCH'
    );

    $request = new self($url, array_merge($defaults, $params));
    return $request->response();

  }

  /**
   * Static method to send a DELETE request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function delete($url, $params = array()) {

    $defaults = array(
      'method' => 'DELETE'
    );

    $request = new self($url, array_merge($defaults, $params));
    return $request->response();

  }

  /**
   * Static method to send a HEAD request
   *
   * @param string $url
   * @param array $params
   * @return object Response
   */
  public static function head($url, $params = array()) {

    $defaults = array(
      'method' => 'HEAD'
    );

    $request = new self($url, array_merge($defaults, $params));
    return $request->response();

  }

  /**
   * Static method to send a HEAD request
   * which only returns an array of headers
   *
   * @param string $url
   * @param array $params
   * @return array
   */
  public static function headers($url, $params = array()) {
    $request = static::head($url, $params);
    return array_merge($request->headers(), $request->info());
  }

  /**
   * Internal method to handle post field data
   *
   * @param mixed $data
   * @return mixed
   */
  protected function postfields($data) {

    if(is_object($data) || is_array($data)) {
      return http_build_query($data);
    } else {
      return $data;
    }

  }

}

class RemoteResponse extends Obj {

  public function __toString() {
    return (string)$this->content;
  }

}