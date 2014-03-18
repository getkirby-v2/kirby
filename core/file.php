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

    $this->site  = $files->site();
    $this->page  = $files->page();
    $this->files = $files;
    $this->root  = $this->files->page()->root() . DS . $filename;

    parent::__construct($this->root);

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
    return $this->site;
  }

  /**
   * Returns the parent files collection
   *
   * @return Files
   */
  public function files() {
    return $this->site;
  }

  public function siblings() {
    return $this->files->not($this->filename);
  }

  /**
   * Returns the absolute URL for the file
   * 
   * @return string
   */
  public function url() {
    return $this->site->options['url.content'] . '/' . $this->page->diruri() . '/' . $this->filename;
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
   * Magic getter for all meta fields
   * 
   * @return Field
   */
  public function __call($key, $arguments = null) {
    return $this->meta()->get($key, $arguments);
  }

  /**
   * Makes it possible to echo the entire object
   * 
   * @return string
   */
  public function __toString() {
    return $this->root;
  }

}