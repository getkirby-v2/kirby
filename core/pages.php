<?php

/**
 * Pages
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class PagesAbstract extends Collection {

  static public $methods = array();

  /**
   * Constructor
   */
  public function __construct($data = array()) {
    foreach($data as $object) {
      $this->add($object);
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

  /**
   * Adds a single page object to the
   * collection by id or the entire object
   *
   * @param mixed $page
   */
  public function add($page) {

    if(is_a($page, 'Collection')) {
      foreach($page as $object) $this->add($object);
    } else if(is_string($page) and $object = page($page)) {
      $this->data[$object->id()] = $object;
    } else if(is_a($page, 'Page')) {
      $this->data[$page->id()] = $page;
    }

    return $this;

  }

  /**
   * Returns a new collection of pages without the given pages
   *
   * @param args any number of uris or page elements, passed as individual arguments
   * @return object a new collection without the pages
   */
  public function not() {
    $collection = clone $this;
    foreach(func_get_args() as $uri) {
      if(is_array($uri) or $uri instanceof Traversable) {
        foreach($uri as $u) {
          $collection = $collection->not($u);
        }
      } else if(is_a($uri, 'Page')) {
        // unset by Page object
        unset($collection->data[$uri->id()]);
      } else if(isset($collection->data[$uri])) {
        // unset by URI
        unset($collection->data[$uri]);
      } else if($page = $collection->findBy('uid', $uri)) {
        // unset by UID
        unset($collection->data[$page->id()]);
      }
    }
    return $collection;
  }

  public function find() {

    $args = func_get_args();

    if(count($args) === 1 and is_array($args[0])) {
      $args = $args[0];
    }

    if(!count($args)) {
      return false;
    }

    if(count($args) > 1) {
      $pages = new static();
      foreach($args as $id) {
        if($page = $this->find($id)) {
          $pages->data[$page->id()] = $page;
        }
      }
      return $pages;
    } else {

      // get the first argument and remove slashes
      $id = trim($args[0], '/');

      // fast access to direct uris
      return isset($this->data[$id]) ? $this->data[$id] : null;

    }

  }

  /**
   * Find a single page by a given value
   *
   * @param string $field
   * @param string $value
   * @return Page
   */
  public function findBy($field, $value) {
    foreach($this->data as $page) {
      if($page->$field() == $value) return $page;
    }
    return false;
  }

  /**
   * Find the open page in a set
   *
   * @return Page
   */
  public function findOpen() {
    return $this->findBy('isOpen', true);
  }

  /**
   * Filters the collection by visible pages
   *
   * @return Children
   */
  public function visible() {
    $collection = clone $this;
    return $collection->filterBy('isVisible', true);
  }

  /**
   * Filters the collection by invisible pages
   *
   * @return Children
   */
  public function invisible() {
    $collection = clone $this;
    return $collection->filterBy('isInvisible', true);
  }

  /**
   * Checks if a page is in a set of children
   *
   * @param Page | string $page
   * @return boolean
   */
  public function has($page) {
    $uri = is_string($page) ? $page : $page->id();
    return parent::has($uri);
  }

  /**
   * Native search method to search for anything within the collection
   */
  public function search($query, $params = array()) {

    if(is_string($params)) {
      $params = array('fields' => str::split($params, '|'));
    }

    $defaults = array(
      'minlength' => 2,
      'fields'    => array(),
      'words'     => false,
      'score'     => array()
    );

    $options     = array_merge($defaults, $params);
    $collection  = clone $this;
    $searchwords = preg_replace('/(\s)/u',',', $query);
    $searchwords = str::split($searchwords, ',', $options['minlength']);

    if(!empty($options['stopwords'])) {
      $searchwords = array_diff($searchwords, $options['stopwords']);
    }

    if(empty($searchwords)) return $collection->limit(0);

    $searchwords = array_map(function($value) use($options) {
      return $options['words'] ? '\b' . preg_quote($value) . '\b' : preg_quote($value);
    }, $searchwords);

    $preg    = '!(' . implode('|', $searchwords) . ')!i';
    $results = $collection->filter(function($page) use($query, $searchwords, $preg, $options) {

      $data = $page->content()->toArray();
      $keys = array_keys($data);

      if(!empty($options['fields'])) {
        $keys = array_intersect($keys, $options['fields']);
      }

      $page->searchHits  = 0;
      $page->searchScore = 0;

      foreach($keys as $key) {

        $score = a::get($options['score'], $key, 1);
        
        // check for a match
        if($matches = preg_match_all($preg, $data[$key], $r)) {

          $page->searchHits  += $matches;
          $page->searchScore += $matches * $score;

          // check for full matches
          if($matches = preg_match_all('!' . preg_quote($query) . '!i', $data[$key], $r)) {
            $page->searchScore += $matches * $score;
          }

        }

      }

      return $page->searchHits > 0 ? true : false;

    });

    $results = $results->sortBy('searchScore', SORT_DESC);

    return $results;

  }

  /**
   * Returns files from all pages
   *
   * @return object A collection of all files of the pages (not of their subpages)
   */
  public function files() {
    
    $files = new Collection();

    foreach($this->data as $page) {
      foreach($page->files() as $file) {
        $files->append($page->id() . '/' . strtolower($file->filename()), $file);        
      }
    }

    return $files;

  }

  // File type filters
  public function images()    { return $this->files()->filterBy('type', 'image');    }
  public function videos()    { return $this->files()->filterBy('type', 'video');    }
  public function documents() { return $this->files()->filterBy('type', 'document'); }
  public function audio()     { return $this->files()->filterBy('type', 'audio');    }
  public function code()      { return $this->files()->filterBy('type', 'code');     }
  public function archives()  { return $this->files()->filterBy('type', 'archive');  }

  /**
   * Groups the pages by a given field
   *
   * @param string $field
   * @param bool   $i (ignore upper/lowercase for group names)
   * @return object A collection with an item for each group and a Pages object for each group
   */
  public function groupBy($field, $i = true) {

    $groups = array();

    foreach($this->data as $key => $item) {

      $value = $item->content()->get($field)->value();

      // make sure that there's always a proper value to group by
      if(!$value) throw new Exception('Invalid grouping value for key: ' . $key);

      // ignore upper/lowercase for group names
      if($i) $value = str::lower($value);

      if(!isset($groups[$value])) {
        // create a new entry for the group if it does not exist yet
        $groups[$value] = new Pages(array($key => $item));
      } else {
        // add the item to an existing group
        $groups[$value]->set($key, $item);
      }

    }

    return new Collection($groups);

  }

  /**
   * Converts the pages collection
   * into a plain array
   * 
   * @param closure $callback Filter callback for each item
   * @return array
   */
  public function toArray($callback = null) {
    $data = array();
    foreach($this as $page) {
      $data[] = is_string($page) ? $page : $page->toArray($callback);
    }
    return $data;
  }

  /**
   * Converts the pages collection
   * into a json string
   * 
   * @param closure $callback Filter callback for each item
   * @return string
   */
  public function toJson($callback = null) {
    return json_encode($this->toArray($callback));
  }

}
