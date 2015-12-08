<?php

/**
 * Children
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class ChildrenAbstract extends Pages {

  protected $page = null;
  protected $cache = array();

  /**
   * Constructor
   */
  public function __construct($page) {
    $this->page = $page;
  }

  /**
   * Creates a new Page object and adds it to the collection
   */
  public function add($dirname) {
    $page = new Page($this->page, $dirname);
    $this->data[$page->id()] = $page;
    return $page;
  }

  /**
   * Creates a new subpage
   *
   * @param string $uid
   * @param string $template
   * @param array $data
   */
  public function create($uid, $template, $data = array()) {
    $page = page::create($this->page->id() . '/' . $uid, $template, $data);
    $this->data[$page->id()] = $page;
    return $page;
  }

  /**
   * Returns the parent page
   *
   * @return Page
   */
  public function page() {
    return $this->page;
  }

  /**
   * Returns the Children of Children
   *
   * @return Children
   */
  public function children() {
    $grandChildren = new Children($this->page);
    foreach($this->data as $page) {
      foreach($page->children() as $subpage) {
        $grandChildren->data[$subpage->id()] = $subpage;
      }
    }
    return $grandChildren;
  }

  /**
   * Find a specific page by its uri
   *
   * @return Page or false
   */
  public function find() {

    $args = func_get_args();

    if(!count($args)) {
      return false;
    } else if(count($args) > 1) {
      $collection = new Children($this->page);
      foreach($args as $id) {
        if($page = $this->find($id)) {
          $collection->data[$page->id()] = $page;
        }
      }
      return $collection;
    } else {

      // get the first argument and remove slashes
      $id = trim($args[0], '/');

      // build the direct uri
      $directId = trim($this->page()->id() . '/' . $id, '/');

      // fast access to direct uris
      if(isset($this->data[$directId])) return $this->data[$directId];

      $path = explode('/', $id);
      $obj  = $this;
      $page = false;

      foreach($path as $uid) {

        $id = ltrim($obj->page()->id() . '/' . $uid, '/');

        if(!isset($obj->data[$id])) return false;

        $page = $obj->data[$id];
        $obj  = $page->children();

      }

      return $page;

    }

  }

  /**
   * Finds pages by it's unique URI
   *
   * @param mixed $uri Either a single URI string or an array of URIs
   * @param string $use The field, which should be used (uid or slug)
   * @return mixed Either a Page object, a Pages object for multiple pages or null if nothing could be found
   */
  public function findByURI() {

    $args = func_get_args();

    if(count($args) == 0) {
      return false;
    } else if(count($args) > 1) {
      $collection = new Children($this->page);
      foreach($args as $uri) {
        $page = $this->findByURI($uri);
        if($page) $collection->data[$page->id()] = $page;
      }
      return $collection;
    } else {

      // get the first argument and remove slashes
      $uri   = trim($args[0], '/');
      $array = str::split($uri, '/');
      $obj   = $this;
      $page  = false;

      foreach($array as $p) {

        $next = $obj->findBy('slug', $p);

        if(!$next) break;

        $page = $next;
        $obj  = $next->children();

      }

      return ($page and $page->slug() != a::last($array)) ? false : $page;

    }

  }

  /**
   * Creates a clean one-level collection with all
   * pages, subpages, subsubpages, etc.
   *
   * @param object Pages object for recursive indexing
   * @return Children
   */
  public function index(Children $obj = null) {

    if(is_null($obj)) {
      if(isset($this->cache['index'])) return $this->cache['index'];
      $this->cache['index'] = new Children($this->page);
      $obj = $this;
    }

    foreach($obj->data as $key => $page) {
      $this->cache['index']->data[$page->uri()] = $page;
      $this->index($page->children());
    }

    return $this->cache['index'];

  }

}
