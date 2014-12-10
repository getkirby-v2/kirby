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
      if(is_array($uri)) {
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

    if(!count($args)) {
      return false;
    } else if(count($args) > 1) {
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
    return isset($this->data[$uri]);
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

}