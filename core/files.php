<?php

/**
 * Files
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class FilesAbstract extends Collection {

  static public $methods = array();

  public $kirby = null;
  public $site  = null;
  public $page  = null;

  public function __construct($page) {
    $this->kirby = $page->kirby;
    $this->site  = $page->site;
    $this->page  = $page;
    $inventory   = $page->inventory();

    foreach($inventory['files'] as $filename) {
      $file = new File($this, $filename);
      $this->data[strtolower($file->filename())] = $file;
    }
  }

  public function __call($method, $arguments) {

    if(isset(static::$methods[$method])) {
      array_unshift($arguments, clone $this);
      return call(static::$methods[$method], $arguments);
    } else {
      return $this->get($method);
    }

  }

  public function kirby() {
    return $this->kirby;
  }

  public function site() {
    return $this->site;
  }

  public function page() {
    return $this->page;
  }

  public function find() {

    $args = func_get_args();

    if(!count($args)) {
      return false;
    } 

    if(count($args) === 1 and is_array($args[0])) {
      $args = $args[0];
    }

    if(count($args) > 1) {
      $files = clone $this;
      $files->data = array();
      foreach($args as $filename) {
        $file = $this->find($filename);
        if(!empty($file)) {
          $files->data[$filename] = $file;
        }
      }
      return $files;
    } else {
      $filename = strtolower($args[0]);
      return isset($this->data[$filename]) ? $this->data[$filename] : null;
    }

  }

  /**
   * Returns a new collection of files without the given files
   *
   * @param args any number of filenames or file objects, passed as individual arguments
   * @return object a new collection without the files
   */
  public function not() {
    $collection = clone $this;
    foreach(func_get_args() as $filename) {
      if(is_array($filename) or $filename instanceof Traversable) {
        foreach($filename as $f) {
          $collection = $collection->not($f);
        }
      } else if(is_a($filename, 'Media')) {
        // unset by Media object
        unset($collection->data[strtolower($filename->filename())]);
      } else {
        unset($collection->data[strtolower($filename)]);
      }
    }
    return $collection;
  }

  /**
   * Converts the files collection
   * into a plain array
   * 
   * @param closure $callback Filter callback for each item
   * @return array
   */
  public function toArray($callback = null) {

    $data = array();

    foreach($this as $file) {
      $data[] = $file->toArray($callback);
    }

    return $data;

  }

  /**
   * Converts the files collection
   * into a json string
   * 
   * @param closure $callback Filter callback for each item
   * @return string
   */
  public function toJson($callback = null) {
    return json_encode($this->toArray($callback));
  }

}