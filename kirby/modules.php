<?php

namespace Kirby;

use A;
use F;

class Modules {

  public $modules;

  public function __construct() {
    $this->modules = array(
      // core
      'controllers' => array(),
      'languages'   => array(),
      'models'      => array(),
      'snippets'    => array(),
      'tags'        => array(),
      'templates'   => array(),

      // panel
      'blueprints'  => array(),
      'fields'      => array(),
    );
  }

  public function register($type, $dir) {
    if(!isset($this->modules[$type])) return false;
    array_push($this->modules[$type], $dir);
  }

  public function __call($method, $arguments) {
    return a::get($this->modules, $method, null);
  }

  public function findFile($type, $file, $extensions, $default) {
    if(!isset($this->modules[$type])) return null;

    $dirs = a::get($this->modules, $type);
    $exts = $this->extensions($extensions);
    array_unshift($dirs, $default);

    foreach($dirs as $dir) {
      if($return = f::resolve($dir . DS . $file, $exts)) break;
    }

    return f::exists($return) ? $return : $default . DS . $file . $exts[0];
  }

  protected function extensions($extensions = array()) {
    return array_map(function($ext) {
      return ltrim($ext, '.');
    }, (array)$extensions);
  }

}
