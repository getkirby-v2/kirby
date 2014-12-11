<?php

namespace Kirby;

use R;
use Server;
use URL;

class Urls {

  public function index() {

    if(isset($this->index)) return $this->index;

    if(r::cli()) {
      return $this->index = '/';
    } else {
      return $this->index = url::base() . preg_replace('!\/index\.php$!i', '', server::get('SCRIPT_NAME'));
    }

  }

  public function content() {
    return isset($this->content) ? $this->content : url::makeAbsolute('content', $this->index);
  }

  public function thumbs() {
    return isset($this->thumbs) ? $this->thumbs : url::makeAbsolute('thumbs', $this->index);
  }

  public function assets() {
    return isset($this->assets) ? $this->assets : url::makeAbsolute('assets', $this->index);
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

}