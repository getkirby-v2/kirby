<?php

abstract class AssetAbstract extends Media {
  
  public $kirby    = null;  
  public $attr     = [];
  public $original = null;

  use Kirby\Traits\Image;

  public function __construct($path) {
    $this->kirby = kirby::instance();
    if(is_a($path, 'Media')) {
      parent::__construct($path->root(), $path->url());
    } else {
      parent::__construct(
        url::isAbsolute($path) ? null : $this->kirby->roots()->index() . DS . ltrim($path, DS), 
        url::makeAbsolute($path)
      );
    }
  }

  public function attr($attr = null) {
    if($attr === null) {
      return $this->attr;
    } else {
      $this->attr = $attr;      
      return $this;
    }
  }

  public function original($original = null) {
    if($original === null) {
      return $this->original === null ? $this : $this->original;
    } else {
      $this->original = $original;      
      return $this;
    }
  }

}