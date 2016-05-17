<?php

abstract class AssetAbstract extends Media {
  
  public $kirby = null;  

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

}