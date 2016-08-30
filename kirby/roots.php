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
    return isset($this->config) ? $this->config : $this->site() . DS . 'config';
  }

  public function accounts() {
    return isset($this->accounts) ? $this->accounts : $this->site() . DS . 'accounts';
  }

  public function roles() {
    return isset($this->roles) ? $this->roles : $this->site() . DS . 'roles';
  }

  public function blueprints() {
    return isset($this->blueprints) ? $this->blueprints : $this->site() . DS . 'blueprints';
  }

  public function plugins() {
    return isset($this->plugins) ? $this->plugins : $this->site() . DS . 'plugins';
  }

  public function cache() {
    return isset($this->cache) ? $this->cache : $this->site() . DS . 'cache';
  }

  public function tags() {
    return isset($this->tags) ? $this->tags : $this->site() . DS . 'tags';
  }

  public function fields() {
    return isset($this->fields) ? $this->fields : $this->site() . DS . 'fields';
  }

  public function widgets() {
    return isset($this->widgets) ? $this->widgets : $this->site() . DS . 'widgets';
  }

  public function controllers() {
    return isset($this->controllers) ? $this->controllers : $this->site() . DS . 'controllers';
  }

  public function models() {
    return isset($this->models) ? $this->models : $this->site() . DS . 'models';
  }

  public function templates() {
    return isset($this->templates) ? $this->templates : $this->site() . DS . 'templates';
  }

  public function snippets() {
    return isset($this->snippets) ? $this->snippets : $this->site() . DS . 'snippets';
  }

  public function languages() {
    return isset($this->languages) ? $this->languages : $this->site() . DS . 'languages';
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return [
      'index'   => $this->index(),
      'kirby'   => $this->kirby(),
      'content' => $this->content(),
      'site'    => $this->site(),
      'cache'   => $this->cache(),
      'thumbs'  => $this->thumbs(),
      'assets'  => $this->assets(),
      'autocss' => $this->autocss(),
      'autojs'  => $this->autojs(),
      'avatars' => $this->avatars(),
    ];
  }

}