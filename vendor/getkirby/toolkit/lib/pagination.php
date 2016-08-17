<?php

/**
 * Pagination
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Pagination {

  // configuration
  public static $defaults = array(
    'variable'      => 'page',
    'method'        => 'param',
    'omitFirstPage' => true,
    'page'          => false,
    'url'           => null,
    'redirect'      => false,
  );

  // options
  protected $options = array();

  // the current page
  protected $page = null;

  // total count of items
  protected $count = 0;

  // the number of displayed rows
  protected $limit = 0;

  // the total number of pages
  protected $pages = 0;

  // the offset for the slice function
  protected $offset = 0;

  // the range start for ranged pagination
  protected $rangeStart = 0;

  // the range end for ranged pagination
  protected $rangeEnd = 0;

  /**
   * Constructor
   *
   * @param mixed $count Either an integer or a Collection
   * @param int $limit The number of items per page
   * @param array $params Additional parameters to control the pagination object
   */
  public function __construct($count, $limit, $params = array()) {

    // You can still pass an entire collection
    if($count instanceof Collection) {
      $count = $count->count();
    }

    $this->options = array_merge(static::$defaults, $params);
    $this->count   = (int)$count;
    $this->limit   = (int)$limit;
    $this->pages   = (int)ceil($this->count / $this->limit);
    $this->offset  = (int)(($this->page()-1) * $this->limit);

  }

  /**
   * Returns the current page number
   *
   * @return int
   */
  public function page() {

    if(!is_null($this->page)) return $this->page;

    if($this->options['page']) {
      $this->page = $this->options['page'];
    } else {
      $this->page = ($this->options['method'] == 'query') ? get($this->options['variable']) : param($this->options['variable']);
    }

    // make sure the page is an int
    $this->page = intval($this->page);

    // set the first page correctly
    if($this->page == 0) {
      $this->page = 1;
    }

    // sanitize the page if too low
    if($this->page < 1) {
      $this->redirect();
      $this->page = 1;
    }

    // sanitize the page if too high
    if($this->page > $this->pages && $this->count > 0) {
      $this->redirect();
      $this->page = $this->lastPage();
    }

    // return the sanitized page number
    return $this->page;

  }

  /**
   * Redirects to an error page if the redirect option is set
   * and the pagination is beyond the allowed boundaries
   */
  public function redirect() {
    if($redirect = $this->options['redirect']) {
      go($redirect);
    }
  }

  /**
   * Returns the total number of pages
   *
   * @return int
   */
  public function countPages() {
    return $this->pages;
  }

  /**
   * Alternative for countPages()
   *
   * @return int
   */
  public function pages() {
    return $this->pages;
  }

  /**
   * Returns the current offset
   * This is used for the slice() method together with
   * the limit to get the correct items from collections
   *
   * @return int
   */
  public function offset() {
    return $this->offset;
  }

  /**
   * Returns the chosen limit
   * This is used for the slice() method together with
   * the offset to get the correct items from collections
   *
   * @return int
   */
  public function limit() {
    return $this->limit;
  }

  /**
   * Checks if multiple pages are needed
   * or if the collection can be displayed on a single page
   *
   * @return boolean
   */
  public function hasPages() {
    return $this->countPages() > 1;
  }

  /**
   * Returns the total number of items in the collection
   *
   * @return int
   */
  public function countItems() {
    return $this->count;
  }

  /**
   * Alternative for countItems()
   *
   * @return int
   */
  public function items() {
    return $this->count;
  }

  /**
   * Returns a page url for any given page number
   *
   * @param int $page The page number
   * @return string The url
   */
  public function pageURL($page) {

    if($this->options['method'] == 'query') {

      $query = url::query($this->options['url']);

      if($page == 1 && $this->options['omitFirstPage']) {
        unset($query[$this->options['variable']]);
      } else {
        $query[$this->options['variable']] = $page;
      }

      return url::build(array('query' => $query), $this->options['url']);

    } else {

      $params = url::params($this->options['url']);

      if($page == 1 && $this->options['omitFirstPage']) {
        unset($params[$this->options['variable']]);
      } else {
        $params[$this->options['variable']] = $page;
      }

      return url::build(array('params' => $params), $this->options['url']);

    }

  }

  /**
   * Returns the number of the first page
   *
   * @return int
   */
  public function firstPage() {
    return 1;
  }

  /**
   * Checks if the current page is the first page
   *
   * @return boolean
   */
  public function isFirstPage() {
    return ($this->page == $this->firstPage()) ? true : false;
  }

  /**
   * Returns the url for the first page
   *
   * @return string
   */
  public function firstPageURL() {
    return $this->pageURL(1);
  }

  /**
   * Returns the number of the last page
   *
   * @return int
   */
  public function lastPage() {
    return $this->pages;
  }

  /**
   * Checks if the current page is the last page
   *
   * @return boolean
   */
  public function isLastPage() {
    return $this->page == $this->lastPage();
  }

  /**
   * Returns the url for the last page
   *
   * @return string
   */
  public function lastPageURL() {
    return $this->pageURL($this->lastPage());
  }

  /**
   * Returns the number of the previous page
   *
   * @return int
   */
  public function prevPage() {
    return $this->hasPrevPage() ? $this->page-1 : $this->page;
  }

  /**
   * Returns the url for the previous page
   *
   * @return string
   */
  public function prevPageURL() {
    return $this->pageURL($this->prevPage());
  }

  /**
   * Checks if there's a previous page
   *
   * @return boolean
   */
  public function hasPrevPage() {
    return $this->page > 1;
  }

  /**
   * Returns the number of the next page
   *
   * @return int
   */
  public function nextPage() {
    return $this->hasNextPage() ? $this->page+1 : $this->page;
  }

  /**
   * Returns the url for the next page
   *
   * @return string
   */
  public function nextPageURL() {
    return $this->pageURL($this->nextPage());
  }

  /**
   * Checks if there's a next page
   *
   * @return boolean
   */
  public function hasNextPage() {
    return $this->page < $this->pages;
  }

  /**
   * Returns the index number of the first item on the current page
   * Can be used to display something like
   * "Currently showing 1 - 10 of 123 items"
   *
   * @return int
   */
  public function numStart() {
    return $this->offset+1;
  }

  /**
   * Returns the index number of the last item on the current page
   * Can be used to display something like
   * "Currently showing 1 - 10 of 123 items"
   *
   * @return int
   */
  public function numEnd() {
    $end = $this->offset+$this->limit;
    if($end > $this->items()) $end = $this->items();
    return $end;
  }

  /**
   * Creates a range of page numbers for Google-like pagination
   *
   * @return array
   */
  public function range($range=5) {

    if($this->countPages() <= $range) {
      $this->rangeStart = 1;
      $this->rangeEnd   = $this->countPages();
      return range($this->rangeStart, $this->rangeEnd);
    }

    $this->rangeStart = $this->page - (int)floor($range/2);
    $this->rangeEnd   = $this->page + (int)floor($range/2);

    if($this->rangeStart <= 0) {
      $this->rangeEnd += abs($this->rangeStart)+1;
      $this->rangeStart = 1;
    }

    if($this->rangeEnd > $this->countPages()) {
      $this->rangeStart -= $this->rangeEnd-$this->countPages();
      $this->rangeEnd = $this->countPages();
    }

    return range($this->rangeStart,$this->rangeEnd);

  }

  /**
   * Returns the first page of the created range
   *
   * @return int
   */
  public function rangeStart() {
    return $this->rangeStart;
  }

  /**
   * Returns the last page of the created range
   *
   * @return int
   */
  public function rangeEnd() {
    return $this->rangeEnd;
  }

  /**
   * Returns the most important pagination data into a readable array
   *
   * @return array
   */
  public function toArray() {
    return array(
      'page'  => $this->page(),
      'pages' => $this->pages(),
      'items' => $this->countItems(),
    );
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return $this->toArray();
  }

}