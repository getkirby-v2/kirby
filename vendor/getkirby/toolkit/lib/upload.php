<?php

/**
 * Upload
 *
 * File Upload class
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Upload {

  const ERROR_FAILED_UPLOAD       = 0;
  const ERROR_MISSING_TMP_DIR     = 1;
  const ERROR_MISSING_FILE        = 2;
  const ERROR_UNALLOWED_OVERWRITE = 3;
  const ERROR_PARTIAL_UPLOAD      = 4;
  const ERROR_MAX_SIZE            = 5;
  const ERROR_MOVE_FAILED         = 6;
  const ERROR_UNACCEPTED          = 7;

  public $options = array();
  public $error   = null;
  public $file    = null;
  public $to      = null;

  public function __construct($to, $params = array()) {

    $defaults = array(
      'input'     => 'file',
      'index'     => 0,
      'to'        => $to,
      'overwrite' => true,
      'maxSize'   => false,
      'accept'    => null,
    );

    $this->options = array_merge($defaults, $params);

    try {
      $this->move();
      $this->file = new Media($this->to());
    } catch(Exception $e) {
      $this->error = $e;
    }

  }

  public function error() {
    return $this->error;
  }

  public function source() {

    $source = isset($_FILES[$this->options['input']]) ? $_FILES[$this->options['input']] : null;

    // get the correct file out of multiple based on the "index" option
    if($source && is_int($this->options['index']) && is_array($source['name'])) {
      $allSources = $source;
      $source = array();
      
      // get the correct value out of the $values array with all files
      foreach($allSources as $key => $values) {
        $source[$key] = isset($values[$this->options['index']]) ? $values[$this->options['index']] : null;
      }
    }

    // prevent duplicate ios uploads
    // ios automatically uploads all images as image.jpg, 
    // which will lead to overwritten duplicates. 
    // this dirty hack will simply add a uniqid between the 
    // name and the extension to avoid duplicates
    if($source && f::name($source['name']) == 'image' && detect::ios()) {
      $source['name'] = 'image-' . uniqid() . '.' . ltrim(f::extension($source['name']), '.');
    }

    return $source;

  }

  public function to() {

    if(!is_null($this->to)) return $this->to;

    $source        = $this->source();
    $name          = f::name($source['name']);
    $extension     = f::extension($source['name']);
    $safeName      = f::safeName($name);
    $safeExtension = str_replace('jpeg', 'jpg', str::lower($extension));

    if(empty($safeExtension)) {
      $safeExtension = f::mimeToExtension(f::mime($source['tmp_name']));
    }

    return $this->to = str::template($this->options['to'], array(
      'name'          => $name,
      'filename'      => $source['name'],
      'safeName'      => $safeName,
      'safeFilename'  => $safeName . r(!empty($safeExtension), '.' . $safeExtension),
      'extension'     => $extension,
      'safeExtension' => $safeExtension
    ));

  }

  /**
   * Returns the maximum accepted file size
   * 
   * @return int
   */
  public function maxSize() {
    $sizes = array(detect::maxPostSize(), detect::maxUploadSize());
    if($this->options['maxSize']) {
      $sizes[] = $this->options['maxSize'];
    }
    return min($sizes);
  }

  public function file() {
    return $this->file;
  }

  protected function move() {

    $source = $this->source();

    if(is_null($source['name']) || is_null($source['tmp_name'])) {
      $this->fail(static::ERROR_MISSING_FILE);
    }

    if($source['error'] !== 0) {

      switch($source['error']) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $this->fail(static::ERROR_MAX_SIZE);
        case UPLOAD_ERR_PARTIAL:
          $this->fail(static::ERROR_PARTIAL_UPLOAD);
        case UPLOAD_ERR_NO_FILE:
          $this->fail(static::ERROR_MISSING_FILE);
        case UPLOAD_ERR_NO_TMP_DIR:
          $this->fail(static::ERROR_MISSING_TMP_DIR);
        case UPLOAD_ERR_CANT_WRITE:
          $this->fail(static::ERROR_MOVE_FAILED);
        case UPLOAD_ERR_EXTENSION:
          $this->fail(static::ERROR_UNACCEPTED);
        default: 
          $this->fail(static::ERROR_FAILED_UPLOAD);
      }

    }

    if(file_exists($this->to()) && $this->options['overwrite'] === false) {
      $this->fail(static::ERROR_UNALLOWED_OVERWRITE);
    }

    if($this->options['maxSize'] && $source['size'] > $this->options['maxSize']) {
      $this->fail(static::ERROR_MAX_SIZE);
    }

    if(is_callable($this->options['accept'])) {
      $accepted = call($this->options['accept'], new Media($source['tmp_name']));
      if($accepted === false) {
        $this->fail(static::ERROR_UNACCEPTED);
      }
    }

    if(!@move_uploaded_file($source['tmp_name'], $this->to())) {
      $this->fail(static::ERROR_MOVE_FAILED);
    }

  }

  protected function messages() {
    return array(
      static::ERROR_MISSING_FILE        => 'The file is missing',
      static::ERROR_MISSING_TMP_DIR     => 'The /tmp directory is missing on your server',
      static::ERROR_FAILED_UPLOAD       => 'The upload failed',
      static::ERROR_PARTIAL_UPLOAD      => 'The file has been only been partially uploaded',
      static::ERROR_UNALLOWED_OVERWRITE => 'The file exists and cannot be overwritten',
      static::ERROR_MAX_SIZE            => 'The file is too big. The maximum size is ' . f::niceSize($this->maxSize()),
      static::ERROR_MOVE_FAILED         => 'The file could not be moved',
      static::ERROR_UNACCEPTED          => 'The file is not accepted by the server'
    );
  }

  protected function fail($code) {

    $messages = $this->messages();

    if(!isset($messages[$code])) {
      $code = static::ERROR_FAILED_UPLOAD;
    }

    throw new Error($messages[$code], $code);

  }

}