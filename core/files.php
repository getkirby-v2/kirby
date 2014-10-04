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

  public function add($filename) {
    $file = new File($this, $filename);
    $this->data[$file->filename()] = $file;
    return $file;
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
      return isset($this->data[$args[0]]) ? $this->data[$args[0]] : null;
    }

  }

}