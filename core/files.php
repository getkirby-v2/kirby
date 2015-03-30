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
    } else if(count($args) > 1) {
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