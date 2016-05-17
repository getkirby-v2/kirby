<?php

namespace Kirby\Traits;

use A;
use Exception;
use Media;
use Str;

/**
 * 
 */
trait Image {

  /**
   * store for the original image file
   * 
   * @var Media|Asset|File
   */
  protected $original;

  /**
   * @param Media $original
   * @return Media|this
   */
  public function original(Media $original = null) {
    if($original === null) {
      return $this->original;      
    } else {
      $this->original = $original;
      return $this;
    }
  }

  /**
   * Creates a thumbnail for the image
   * 
   * @param array $params
   * @return Asset
   */
  public function thumb($params = []) {
    // don't scale thumbs further down
    if($this->original()) {    
      throw new Exception('Thumbnails cannot be modified further');
    } else {
      return $this->kirby->component('thumb')->create($this, $params);
    }
  }

  /**
   * Scales the image if possible
   * 
   * @param int $width
   * @param mixed $height
   * @param mixed $quality
   * @return Asset
   */
  public function resize($width, $height = null, $quality = null) {

    $params = ['width' => $width];

    if($height)  $params['height']  = $height;
    if($quality) $params['quality'] = $quality;

    return $this->thumb($params);

  }

  /**
   * Scales and crops the image if possible
   * 
   * @param int $width
   * @param mixed $height
   * @param mixed $quality
   * @return Asset
   */
  public function crop($width, $height = null, $quality = null) {

    $params = ['width' => $width, 'crop' => true];

    if($height)  $params['height']  = $height;
    if($quality) $params['quality'] = $quality;

    return $this->thumb($params);

  }

  /**
   * Scales the width of the image
   * 
   * @param int $width
   * @param mixed $quality
   * @return Asset
   */
  public function width($width = null, $quality = null) {

    if($width === null) {
      return parent::width();
    }

    $params = ['width' => $width];

    if($quality) $params['quality'] = $quality;

    return $this->thumb($params);

  }
  
  /**
   * Scales the height of the image
   * 
   * @param int $height
   * @param mixed $quality
   * @return Asset
   */
  public function height($height = null, $quality = null) {

    if($height === null) {
      return parent::height();
    }

    $params = ['height' => $height];

    if($quality) $params['quality'] = $quality;

    return $this->thumb($params);

  }

  /**
   * 
   */
  public function ratio($ratio = null) {

    if($ratio === null) {
      return parent::ratio();
    }

    if($this->isLandscape() || $this->isSquare()) {
      $width  = $this->width();
      $height = round($width / $ratio);
    } else {
      $height = $this->height();
      $width  = round($height * $ratio);
    }

    return $this->crop($width, $height);

  }

  /**
   * 
   */
  public function scale($value) {
    return $this->thumb(['width' => $this->width() * $value, 'upscale' => true]);
  }

  /**
   * Converts the image to grayscale
   * 
   * @return Asset
   */
  public function bw() {
    return $this->thumb(['grayscale' => true]);
  }

  /**
   * Blurs the image
   * 
   * @return Asset
   */
  public function blur() {
    return $this->thumb(['blur' => true]);
  }

  /**
   * Checks if the asset is a thumbnail
   * 
   * @return boolean
   */
  public function isThumb() {
    return str::startsWith($this->url(), $this->kirby->urls()->thumbs());    
  }

  /**
   * Check if the file/image has a websafe format
   * 
   * @return boolean
   */
  public function isWebsafe() {
    return in_array(strtolower($this->extension()), ['jpg', 'jpeg', 'gif', 'png']);
  }

  /**
   * Makes it possible to echo the entire object
   *
   * @return string
   */
  public function __toString() {
    if($this->isWebsafe()) {
      return (string)$this->html();      
    } else {
      return (string)$this->root;      
    }
  }

}