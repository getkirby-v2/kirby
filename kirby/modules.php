<?php

namespace Kirby;

use A;
use F;
use Dir;
use Response;

class Modules {

  // core
  public $controllers = array();
  public $languages   = array();
  public $models      = array();
  public $snippets    = array();
  public $tags        = array();
  public $templates   = array();
  public $autocss     = array();
  public $autojs      = array();

  // panel
  public $blueprints  = array();
  public $fields      = array();


  /**
   * Magic getter for module arrays
   */
  public function __call($method, $arguments) {
    return isset($this->{$method}) ? $this->{$method} : null;
  }

  /**
   * Registers a plugin directory for a module type
   */
  public function register($module, $root) {
    if(!isset($this->{$module})) return false;

    // prepare for auto-assets
    if($module === 'autocss' or $module === 'autojs') {
      $route = 'assets/modules/' . sha1($root);
      $this->assets($route, $root);
      $root  = compact('root', 'route');
    }

    array_push($this->{$module}, $root);
    return true;
  }

  /**
   * Creates route for plugin assets
   */
   public function assets($route, $root) {
    if($page = page($route)) return false;

    return kirby()->routes(array(
      array(
        'pattern' => trim($route, '/') . '/(:all)',
        'action'  => function($file) use($root) {
          $path = $root . DS . $file;
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


  public function getAsset($module, $file) {

    if($dirs = $this->{$module}()) {
      foreach($dirs as $dir) {
        $root = $dir['root'] . DS . $file;
        $url  = $dir['route'] . DS . $file;
        if(f::exists($root)) return $url;
      }
    }

    return null;

  }

  public function getFile($module, $file, $ext, $default = null) {

    if($dirs = $this->{$module}()) {
      // $ext as array without dots
      $ext = array_map(function($e) {
        return ltrim($e, '.');
      }, (array)$ext);

      // add default location as possible source
      if(!is_null($default)) array_unshift($dirs, $default);

      foreach($dirs as $dir) {
        $root = f::resolve($dir . DS . $file, $ext);
        if($root) return $root;
      }

    }

    return !is_null($default) ? $default . DS . $file . $ext[0] : null;

  }

  public function allFiles($module, $default = null, $sort = false) {
    $files = array();

    foreach($this->allRoots($module, $default) as $dir) {
      foreach(dir::read($dir) as $file){
        $files[] = $file;
      }
    }

    if($sort === true) sort($files);

    return array_unique($files);
  }

  public function allRoots($module, $default = null) {
    $dirs = $this->{$module}();
    if(is_null($dirs)) $dirs = array();
    if($default) $dirs[] = $default;
    return $dirs;
  }

}
