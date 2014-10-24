<?php

/**
 * Site
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class SiteAbstract extends Page {

  // the current page
  public $page = null;

  /**
   * Constructor
   *
   */
  public function __construct(Kirby $kirby) {

    $this->kirby   = $kirby;
    $this->url     = $kirby->urls()->index();
    $this->depth   = 0;
    $this->uri     = '';
    $this->site    = $this;
    $this->page    = null;

    // build ugly urls if rewriting is disabled
    if($this->kirby->options['rewrite'] === false) {
      $this->url .= '/index.php';
    }

    $this->root    = $kirby->roots()->content();
    $this->dirname = basename($this->root);

  }

  /**
   * Cleans the temporary internal cache
   */
  public function reset() {
    $this->cache = array();
  }

  /**
   * The base diruri is bascially just an empty string
   *
   * @return string
   */
  public function diruri() {
    return '';
  }

  /**
   * Returns the base url for the site
   *
   * @return string
   */
  public function url() {
    return $this->url;
  }

  /**
   * Checks if this object is the main site
   *
   * @return boolean
   */
  public function isSite() {
    return true;
  }

  /**
   * Returns the usable template
   *
   * @return string
   */
  public function template() {
    return 'site';
  }

  /**
   * The site has no template
   *
   * @return boolean
   */
  public function templateFile() {
    return false;
  }

  /**
   * Returns the intended template
   *
   * @return string
   */
  public function intendedTemplate() {
    return 'site';
  }

  /**
   * Again, the site has no template!
   *
   * @return boolean
   */
  public function intendedTemplateFile() {
    return false;
  }

  /**
   * There can't be a template for the site
   * Didn't you still get it yet?
   *
   * @return boolean
   */
  public function hasTemplate() {
    return false;
  }

  /**
   * Sets the currently active page
   * and returns its page object
   *
   * @param string $uri
   * @return Page
   */
  public function visit($uri = '') {

    $uri = trim($uri, '/');

    if(empty($uri)) {
      return $this->page = $this->homePage();
    } else {
      if($page = $this->children()->find($uri)) {
        return $this->page = $page;
      } else {
        return $this->page = $this->errorPage();
      }
    }

  }

  /**
   * Returns the currently active page or any other page by uri
   *
   * @param string $uri Optional uri to get any page on the site
   * @return Page
   */
  public function page($uri = null) {
    if(is_null($uri)) {
      return is_null($this->page) ? $this->page = $this->homePage() : $this->page;
    } else {
      return $this->children()->find($uri);
    }
  }

  /**
   * Alternative for $this->children()
   *
   * @return Children
   */
  public function pages() {
    return $this->children();
  }

  /**
   * Builds a breadcrumb collection
   *
   * @return Children
   */
  public function breadcrumb() {

    if(isset($this->cache['breadcrumb'])) return $this->cache['breadcrumb'];

    // get all parents and flip the order
    $crumb = $this->page()->parents()->flip();

    // add the home page
    $crumb->prepend($this->homePage()->uri(), $this->homePage());

    // add the active page
    $crumb->append($this->page()->uri(), $this->page());

    return $this->cache['breadcrumb'] = $crumb;

  }

  /**
   * Alternative for $this->page()
   *
   * @return Page
   */
  public function activePage() {
    return $this->page();
  }

  /**
   * Returns the error page object
   *
   * @return Page
   */
  public function errorPage() {
    if(isset($this->cache['errorPage'])) return $this->cache['errorPage'];
    return $this->cache['errorPage'] = $this->children()->find($this->kirby->options['error']);
  }

  /**
   * Returns the home page object
   *
   * @return Page
   */
  public function homePage() {
    if(isset($this->cache['homePage'])) return $this->cache['homePage'];
    return $this->cache['homePage'] = $this->children()->find($this->kirby->options['home']);
  }

  /**
   * Returns the locale for the site
   *
   * @return string
   */
  public function locale() {
    return isset($this->kirby->options['locale']) ? $this->kirby->options['locale'] : 'en_US';
  }

  /**
   * Checks if the site is a multi language site
   *
   * @return boolean
   */
  public function multilang() {
    return false;
  }

  /**
   * Placeholder for multilanguage sites
   */
  public function languages() {
    return null;
  }

  /**
   * Placeholder for multilanguage sites
   */
  public function language() {
    return null;
  }

  /**
   * Placeholder for multilanguage sites
   */
  public function defaultLanguage() {
    return null;
  }

  /**
   * Return the detected language
   */
  public function detectedLanguage() {
    return null;
  }

  /**
   * Returns a collection of all users
   *
   * @return Users
   */
  public function users() {
    return new Users();
  }

  /**
   * Returns the current user
   *
   * @param string $username Optional way to search for a single user
   * @return User
   */
  public function user($username = null) {
    if(is_null($username)) return User::current();
    try {
      return new User($username);
    } catch(Exception $e) {
      return null;
    }
  }

  /**
   * Returns a collection of all roles
   *
   * @return Roles
   */
  public function roles() {
    return new Roles();
  }

  /**
   * Checks if any content of the site has been
   * modified after the given unix timestamp
   * This is mainly used to auto-update the cache
   *
   * @return boolean
   */
  public function wasModifiedAfter($time) {
    return dir::wasModifiedAfter($this->root(), $time);
  }

}