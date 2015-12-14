<?php

/**
 * Kirbytag
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class KirbytagAbstract {

  protected $page;
  protected $kirbytext;
  protected $name;
  protected $html;
  protected $attr = array();

  public function __construct($kirbytext, $name, $tag) {

    if(is_null($kirbytext)) $kirbytext = new Kirbytext('');

    $this->page      = $kirbytext->field->page;
    $this->kirbytext = $kirbytext;
    $this->name      = $name;
    $this->html      = kirbytext::$tags[$name]['html'];

    // get a list with all attributes
    $attributes = isset(kirbytext::$tags[$name]['attr']) ? (array)kirbytext::$tags[$name]['attr'] : array();

    // add the name as first attribute
    array_unshift($attributes, $name);

    if(is_array($tag)) {
      foreach($attributes as $key) {
        if(isset($tag[$key])) $this->attr[$key] = $tag[$key];
      }
    } else {

      // extract any attribute
      preg_match_all('/([a-z0-9_-]+):((?:\s+(?![a-z0-9_-]+:\s+?)\S+|(?:[^()]*\)))+)/is', $tag, $matches);

      $attr = array_combine($matches[1], array_map('trim', $matches[2]));

      // filter on expected attributes
      $this->attr = array_intersect_key($attr, array_flip($attributes));

    }

  }

  /**
   * Returns the parent active page
   *
   * @return object Page
   */
  public function page() {
    return $this->page;
  }

  /**
   * Returns the parent kirbytext object
   *
   * @return object Kirbytext
   */
  public function kirbytext() {
    return $this->kirbytext;
  }

  /**
   * Returns the field object
   *
   * @return object Field
   */
  public function field() {
    return $this->kirbytext->field();
  }

  /**
   * Tries to find all related files for the current page
   *
   * @return object Files
   */
  public function files() {
    return $this->page->files();
  }

  /**
   * Tries to find a file for the given url/uri
   *
   * @param string $url a full path to a file or just a filename for files form the current active page
   * @return object File
   */
  public function file($url) {

    // if this is an absolute url cancel
    if(preg_match('!(http|https)\:\/\/!i', $url)) return false;

    // skip urls without extensions
    if(!preg_match('!\.[a-z0-9]+$!i',$url)) return false;

    // relative url
    if(str::contains($url, '/')) {

      $path     = dirname($url);
      $filename = basename($url);

      if($page = page($path) and $file = $page->file($filename)) {
        return $file;
      } else {
        return false;
      }

    }

    // try to get all files for the current page
    $files = $this->files();

    // cancel if no files are available
    if(!$files) return false;

    // try to find the file
    return $files->find($url);

  }

  /**
   * Returns a specific attribute by key or all attributes 
   * by passing no key at all.
   * 
   * @param mixed $key
   * @param mixed $default
   * @return array
   */
  public function attr($key = null, $default = null) {
    if(is_null($key)) return $this->attr;
    return isset($this->attr[$key]) ? $this->attr[$key] : $default;
  }

  /**
   * Smart getter for the applicable target attribute.
   * This will watch for popup or target attributes and return
   * a proper target value if available.
   *
   * @return string
   */
  public function target() {
    if(empty($this->attr['popup']) and empty($this->attr['target'])) return false;
    return empty($this->attr['popup']) ? $this->attr['target'] : '_blank';
  }

  public function html() {
    if(!is_callable($this->html)) {
      return (string)$this->html;
    } else {
      return call_user_func_array($this->html, array($this));
    }
  }

  public function __toString() {
    return (string)$this->html();
  }

}
