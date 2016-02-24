<?php

namespace Kirby;

use A;
use F;
use Dir;

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

  public function allFiles($type, $default = null, $sort = false) {
    $files = array();

    foreach($this->allRoots($type, $default) as $dir) {
      foreach(dir::read($dir) as $file){
        $files[] = $file;
      }
    }

    if($sort) sort($files);

    return array_unique($files);
  }

  public function allRoots($type, $default = null) {
    $dirs  = $this->{$type}();
    if($default) $dirs[] = $default;
    return $dirs;
  }

  protected function extensions($extensions = array()) {
    return array_map(function($ext) {
      return ltrim($ext, '.');
    }, (array)$extensions);
  }

}
