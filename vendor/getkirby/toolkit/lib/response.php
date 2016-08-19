<?php

/**
 * Response
 *
 * Represents any response coming from a controller's action and takes care of sending an appropriate header
 *
 * @package   Kirby App
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Response {

  // the response content
  protected $content;

  // the format type
  protected $format;

  // the HTTP code
  protected $code;

  /**
   * Constructor
   *
   * @param string $content
   * @param string $format
   * @param int $code Optional HTTP code
   */
  public function __construct($content, $format = 'html', $code = 200) {

    $this->content = $content;
    $this->format  = strtolower($format);
    $this->code    = $code;

    // convert arrays to json
    if(is_array($this->content) && $this->format == 'json') {

      if(defined('JSON_PRETTY_PRINT') && get('pretty')) {
        $this->content = json_encode($this->content, JSON_PRETTY_PRINT);
      } else {
        $this->content = json_encode($this->content);
      }

    }

  }

  /**
   * Sends the correct header for the response
   *
   * @param boolean $send If set to false, the header will be returned
   * @return mixed
   */
  public function header($send = true) {

    $status = header::status($this->code, false);
    $type   = header::type($this->format, 'utf-8', false);

    if(!$send) return $status . PHP_EOL . $type;

    header($status);
    header($type);

  }

  /**
   * Returns the content of this response
   *
   * @return string
   */
  public function content() {
    return $this->content;
  }

  /**
   * Returns the content format
   *
   * @return string
   */
  public function format() {
    return $this->format;
  }

  /**
   * Returns a success response
   *
   * @param string $message
   * @param mixed $data
   * @param mixed $code
   * @return object
   */
  static public function success($message = 'Everything went fine', $data = array(), $code = 200) {
    return new static(array(
      'status'  => 'success',
      'code'    => $code,
      'message' => $message,
      'data'    => $data
    ), 'json', $code);
  }

  /**
   * Returns an error response
   *
   * @param mixed $message Either a message string or an error or errors object
   * @param mixed $code
   * @param mixed $data
   * @return object
   */
  static public function error($message = 'Something went wrong', $code = 400, $data = array()) {
    return new static(array(
      'status'  => 'error',
      'code'    => $code,
      'message' => $message,
      'data'    => $data
    ), 'json', $code);
  }

  /**
   * Converts an array to json and returns it properly
   *
   * @param array $data
   * @return object
   */
  static public function json($data, $code = 200) {
    return new static($data, 'json', $code);
  }

  /**
   * Echos the content
   * and sends the appropriate header
   *
   * @return string
   */
  public function __toString() {
    $this->header();
    return (string)$this->content;
  }

}