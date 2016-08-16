<?php

namespace Kirby;

use R;
use Server;
use URL;

class Urls {

  public $index;
  public $content;
  public $thumbs;
  public $assets;
  public $autocss;
  public $autojs;
  public $avatars;

  public function index() {

    if(isset($this->index)) return $this->index;

    if(r::cli()) {
      $index = '/';
    } else {
      $index = url::base() . preg_replace('!\/index\.php$!i', '', server::get('SCRIPT_NAME'));
    }

    // fix index URL for the Panel
    if(function_exists('panel')) $index = dirname($index);
    return $this->index = $index;

  }

  public function content() {
    return isset($this->content) ? $this->content : url::makeAbsolute('content', $this->index());
  }

  public function thumbs() {
    return isset($this->thumbs) ? $this->thumbs : url::makeAbsolute('thumbs', $this->index());
  }

  public function assets() {
    return isset($this->assets) ? $this->assets : url::makeAbsolute('assets', $this->index());
  }

  public function autocss() {
    return isset($this->autocss) ? $this->autocss : $this->assets() . '/css/templates';
  }

  public function autojs() {
    return isset($this->autojs) ? $this->autojs : $this->assets() . '/js/templates';
  }

  public function avatars() {
    return isset($this->avatars) ? $this->avatars : $this->assets() . '/avatars';
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return [
      'index'   => $this->index(),
      'content' => $this->content(),
      'thumbs'  => $this->thumbs(),
      'assets'  => $this->assets(),
      'autocss' => $this->autocss(),
      'autojs'  => $this->autojs(),
      'avatars' => $this->avatars(),
    ];
  }

}