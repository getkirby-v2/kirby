<?php

/**
 * File
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class FileAbstract extends Media {

  public $kirby;
  public $site;
  public $page;
  public $files;

  /**
   * Constructor
   *
   * @param Files The parent files collection
   * @param string The filename
   */
  public function __construct(Files $files, $filename) {

    $this->kirby = $files->kirby;
    $this->site  = $files->site;
    $this->page  = $files->page;
    $this->files = $files;
    $this->root  = $this->files->page()->root() . DS . $filename;

    parent::__construct($this->root);

  }

  /**
   * Returns the kirby object
   *
   * @return Kirby
   */
  public function kirby() {
    return $this->kirby;
  }

  /**
   * Returns the parent site object
   *
   * @return Site
   */
  public function site() {
    return $this->site;
  }

  /**
   * Returns the parent page object
   *
   * @return Page
   */
  public function page() {
    return $this->page;
  }

  /**
   * Returns the parent files collection
   *
   * @return Files
   */
  public function files() {
    return $this->files;
  }

  /**
   * Returns the full root for the content file
   *
   * @return string
   */
  public function textfile() {
    return $this->page->textfile($this->filename());
  }

  public function siblings() {
    return $this->files->not($this->filename);
  }

  function next() {
    $siblings = $this->files;
    $index    = $siblings->indexOf($this);
    if($index === false) return false;
    return $this->files->nth($index+1);
  }

  function hasNext() {
    return $this->next();
  }

  function prev() {
    $siblings = $this->files;
    $index    = $siblings->indexOf($this);
    if($index === false) return false;
    return $this->files->nth($index-1);
  }

  function hasPrev() {
    return $this->prev();
  }

  /**
   * Returns the absolute URL for the file
   *
   * @return string
   */
  public function url() {
    return $this->page->contentUrl() . '/' . rawurlencode($this->filename);
  }

  /**
   * Get the meta information
   *
   * @return Content
   */
  public function meta() {

    if(isset($this->cache['meta'])) {
      return $this->cache['meta'];
    } else {

      $inventory = $this->page->inventory();
      $file      = isset($inventory['meta'][$this->filename]) ? $this->page->root() . DS . $inventory['meta'][$this->filename] : null;

      return $this->cache['meta'] = new Content($this->page, $file);

    }

  }

  /**
   * Custom modified method for files
   * 
   * @param string $format
   * @return string
   */
  public function modified($format = null, $handler = null) {
    return parent::modified($format, $handler ? $handler : $this->kirby->options['date.handler']);
  }

  /**
   * Magic getter for all meta fields
   *
   * @return Field
   */
  public function __call($key, $arguments = null) {
    return $this->meta()->get($key, $arguments);
  }

  /**
   * Generates a new filename for a given name
   * and makes sure to handle badly given extensions correctly
   * 
   * @param string $name
   * @return string
   */
  public function createNewFilename($name, $safeName = true) {

    $name = basename($safeName ? f::safeName($name) : $name);
    $ext  = f::extension($name);

    // remove possible extensions
    if(in_array($ext, f::extensions())) {
      $name = f::name($name);      
    }

    return trim($name . '.' . $this->extension(), '.');

  }

  /**
   * Renames the file and also its meta info txt
   *
   * @param string $filename
   * @param boolean $safeName
   */
  public function rename($name, $safeName = true) {

    $filename = $this->createNewFilename($name, $safeName);
    $root     = $this->dir() . DS . $filename;

    if(empty($name)) {
      throw new Exception('The filename is missing');
    }

    if($root == $this->root()) return $filename;

    if(file_exists($root)) {
      throw new Exception('A file with that name already exists');
    }

    if(!f::move($this->root(), $root)) {
      throw new Exception('The file could not be renamed');
    }

    $meta = $this->textfile();

    if(file_exists($meta)) {
      f::move($meta, $this->page->textfile($filename));
    }

    // reset the page cache
    $this->page->reset();

    // reset the basics
    $this->root     = $root;
    $this->filename = $filename;
    $this->name     = $name;
    $this->cache    = array();

    cache::flush();

    return $filename;

  }

  public function update($data = array()) {

    $data = array_merge((array)$this->meta()->toArray(), $data);

    foreach($data as $k => $v) {
      if(is_null($v)) unset($data[$k]);
    }

    if(!data::write($this->textfile(), $data, 'kd')) {
      throw new Exception('The file data could not be saved');
    }

    // reset the page cache
    $this->page->reset();

    // reset the file cache
    $this->cache = array();

    cache::flush();
    return true;

  }

  public function delete() {

    // delete the meta file
    f::remove($this->textfile());

    if(!f::remove($this->root())) {
      throw new Exception('The file could not be deleted');
    }

    cache::flush();
    return true;

  }

  /**
   * Converts the entire file object into 
   * a plain PHP array
   * 
   * @param closure $callback Filter callback
   * @return array
   */
  public function toArray($callback = null) {

    $data = parent::toArray();

    // add the meta content
    $data['meta'] = $this->meta()->toArray();

    if(is_null($callback)) {
      return $data;
    } else {
      return array_map($callback, $data);
    }

  }

  /**
   * Makes it possible to echo the entire object
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->root;
  }

}