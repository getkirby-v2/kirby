<?php

namespace Kirby;

use A;
use F;
use Dir;
use Response;

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
      'autocss'     => array(),
      'autojs'      => array(),

      // panel
      'blueprints'  => array(),
      'fields'      => array(),
    );
  }

  /**
   * Registers a plugin directory for a module type
   * @param  [string] $type module type
   * @param  [string] $dir  directory to be included as source
   */
  public function register($type, $dir) {
    if(!isset($this->modules[$type])) return false;

    // prepare for auto-assets
    if(in_array($type, array('autocss', 'autojs'))) {
      $route = 'assets/modules/' . sha1($dir);
      $dir   = array($dir, $route);
      $this->assets($route, $dir);
    }

    array_push($this->modules[$type], $dir);
  }

  public function assets($route, $dir) {
    return $this->route($route, $dir);
  }

  /**
   * Creates route for plugin assets
   * @param  [string] $dir   plugin directory with assets
   * @param  [type] $route   route to point to $dir
   */
  public function route($route, $dir) {
    if($page = page($route)) return false;

    return kirby()->routes(array(
      array(
        'pattern' => trim($route, '/') . '/(:all)',
        'action'  => function($file) use($dir, $route) {
          $path = $dir . DS . $file;
          if(is_file($path)) {
            $extension = substr(strchr($path, '.'), 1);
            return new Response(f::read($path), $extension);
          } else {
            return site()->errorPage();
          }
        }
      )
    ));
  }

  public function __call($method, $arguments) {
    return a::get($this->modules, $method, null);
  }


  public function getAsset($type, $file) {

    if($dirs = $this->{$type}()) {
      foreach($dirs as $dir) {
        $root = $dir[0] . DS . $file;
        $url  = $dir[1] . DS . $file;
        if(f::exists($root)) return $url;
      }

    } else {
      return null;
    }

  }

  public function getFile($type, $file, $extensions, $default = null) {

    if($dirs = $this->{$type}()) {
      // $extensions as array without dots
      $extensions = array_map(function($ext) {
        return ltrim($ext, '.');
      }, (array)$extensions);

      // add default location as possible source
      if(!is_null($default)) {
        array_unshift($dirs, $default);
      }

      foreach($dirs as $dir) {
        $root = f::resolve($dir . DS . $file, $extensions);
        if($root) return $root;
      }

      return $default . DS . $file . $extensions[0];

    } else {
      return null;
    }

  }

  public function allFiles($type, $default = null, $sort = false) {
    $files = array();

    foreach($this->allRoots($type, $default) as $dir) {
      foreach(dir::read($dir) as $file){
        $files[] = $file;
      }
    }

    if($sort === true) {
      sort($files);
    }

    return array_unique($files);
  }

  public function allRoots($type, $default = null) {
    $dirs = $this->{$type}();
    if(is_null($dirs)) $dirs = array();
    if($default) $dirs[] = $default;
    return $dirs;
  }

}
