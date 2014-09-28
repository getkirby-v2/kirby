<?php

namespace Kirby;

use Obj;

class Roots extends Obj {

  public $index;

  public function __construct($index) {
    $this->index = $index;
  }

  public function content() {
    return isset($this->content) ? $this->content : $this->index . DS . 'content';
  }

  public function site() {
    return isset($this->site) ? $this->site : $this->index . DS . 'site';
  }

  public function kirby() {
    return isset($this->kirby) ? $this->kirby : $this->index . DS . 'kirby';
  }

  public function thumbs() {
    return isset($this->thumbs) ? $this->thumbs : $this->index . DS . 'thumbs';
  }

  public function assets() {
    return isset($this->assets) ? $this->assets : $this->index . DS . 'assets';
  }

  public function autocss() {
    return isset($this->autocss) ? $this->autocss : $this->assets() . DS . 'css' . DS . 'templates';
  }

  public function autojs() {
    return isset($this->autojs) ? $this->autojs : $this->assets() . DS . 'js' . DS . 'templates';
  }

  public function avatars() {
    return isset($this->avatars) ? $this->avatars : $this->assets() . DS . 'avatars';
  }

  public function config() {
    return $this->site() . DS . 'config';
  }

  public function accounts() {
    return $this->site() . DS . 'accounts';
  }

  public function blueprints() {
    return $this->site() . DS . 'blueprints';
  }

  public function plugins() {
    return $this->site() . DS . 'plugins';
  }

  public function cache() {
    return $this->site() . DS . 'cache';
  }

  public function tags() {
    return $this->site() . DS . 'tags';
  }

  public function fields() {
    return $this->site() . DS . 'fields';
  }

  public function widgets() {
    return $this->site() . DS . 'widgets';
  }

  public function controllers() {
    return $this->site() . DS . 'controllers';
  }

  public function templates() {
    return $this->site() . DS . 'templates';
  }

  public function snippets() {
    return $this->site() . DS . 'snippets';
  }

  public function languages() {
    return $this->site() . DS . 'languages';
  }

}