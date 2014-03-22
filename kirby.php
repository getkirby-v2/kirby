<?php 

/**
 * Kirby
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Kirby {

  // The site singleton
  static protected $site;

  // The currently active page
  static protected $page;

  // An array with all loaded plugins
  static protected $plugins;

  static public function setup($config = array()) {

    // load all config settings
    static::configure($config);

    // load the cms branch
    static::branch();

    // create a new site object
    return static::$site = $GLOBALS['site'] = new Site(c::$data);

  }

  /**
   * Custom site setup for the panel 
   * 
   * @return Site
   */
  static public function panelsetup() {

    // setup the site object
    $site = static::setup(array(
      'url' => dirname(static::url())
    ));

    $site->visit('/');

    return $site;

  }

  /**
   * Starts the Kirby setup and 
   * returns the content
   * 
   * @return string
   */
  static public function start($config = array()) {

    static::setup($config);

    // start the router
    $router = new Router();
      
    // run the router
    $router->register(static::routes());

    // only use the fragments of the path without params
    $path  = implode('/', (array)url::fragments(detect::path()));
    $route = $router->run($path);

    call($route->action(), $route->arguments());

    if(static::$page) {

      // send all headers for the page
      static::$page->headers();

      // load the template, apply everything and cache it if wanted
      return c::$data['cache'] ? static::cache(static::$page) : static::render(static::$page);

    }

  }

  /**
   * Registers all routes
   * 
   * @return array
   */
  static protected function routes() {

    $routes = array();

    if(static::$site->multilang()) {

      // language resolver
      $routes['languages'] = array(
        'pattern' => '(' . implode('|', static::$site->languages->codes()) . ')/(:all?)',
        'method'  => 'GET|POST',
        'action'  => function($lang, $path = null) {              
          // visit the currently active page for a specific language
          static::$page = static::$site->visit($path, $lang);
        } 
      );

    }

    // all other urls
    $routes['others'] = array(
      'pattern' => '(:all)',
      'method'  => 'GET|POST',
      'action'  => function($path = null) {
        // visit the currently active page
        static::$page = static::$site->visit($path);
      }
    );

    return $routes;

  }

  /**
   * Loads the applicable branch
   */
  static public function branch() {

    // which branch? 
    if(isset(c::$data['languages']) and count((array)c::$data['languages']) > 0) {
      // if there are more than two installed languages, use the multilang branch
      include_once(__DIR__ . DS . 'branches' . DS . 'multilang.php');      
    } else {
      // otherwise load the default branch
      include_once(__DIR__ . DS . 'branches' . DS . 'default.php');      
    }

  }

  /**
   * Returns the proper base url for the installation
   * 
   * @return string 
   */
  static protected function url() {
    // auto-detect the url
    if(empty(c::$data['url'])) {
      c::$data['url'] = url::scheme() . '://' . $_SERVER['HTTP_HOST'] . preg_replace('!\/index\.php$!i', '', $_SERVER['SCRIPT_NAME']);
    }
    return c::$data['url'];
  }

  /**
   * Sets all defaults and loads the user configuration
   * 
   * @param array $config
   */
  static protected function configure($config = array()) {

    // set some defaults
    c::$data['root.kirby']   = __DIR__;
    c::$data['root.index']   = c::$data['root'] = dirname(__DIR__);
    c::$data['root.content'] = c::$data['root.index'] . DS . 'content';
    c::$data['root.site']    = c::$data['root.index'] . DS . 'site';
    
    // the default timezone
    c::$data['timezone'] = 'UTC';

    // disable the cache by default
    c::$data['cache'] = false;

    // pass the config vars from the constructor
    // to be able to set all roots
    c::$data = array_merge(c::$data, $config);

    // set the subroots for site
    c::$data['root.cache']       = c::$data['root.site']  . DS . 'cache';
    c::$data['root.plugins']     = c::$data['root.site']  . DS . 'plugins';
    c::$data['root.templates']   = c::$data['root.site']  . DS . 'templates';
    c::$data['root.snippets']    = c::$data['root.site']  . DS . 'snippets';
    c::$data['root.controllers'] = c::$data['root.site']  . DS . 'controllers';
    c::$data['root.config']      = c::$data['root.site']  . DS . 'config';
    c::$data['root.tags']        = c::$data['root.site']  . DS . 'tags';
    c::$data['root.blueprints']  = c::$data['root.site']  . DS . 'blueprints';
    c::$data['root.accounts']    = c::$data['root.site']  . DS . 'accounts';

    // load the user config
    if(file_exists(c::$data['root.config'] . DS . 'config.php')) include_once(c::$data['root.config'] . DS . 'config.php');

    // pass the config vars from the constructor again to overwrite
    // stuff from the user config
    c::$data = array_merge(c::$data, $config);

    // detect and store the url
    static::url();

    // default url handler        
    if(empty(c::$data['url.to'])) {
      c::$data['url.to'] = function($url = '') {

        // don't convert absolute urls
        if(url::isAbsolute($url)) return $url;

        // clean the uri
        $url = trim($url, '/');

        // don't do anything if the home url is blank
        if(empty(url::$home)) return $url;

        // return the absolute url for the given uri by prepending the home url
        return empty($url) ? url::$home : url::$home . '/' . $url;

      };
    }

    // auto css and js setup
    if(!isset(c::$data['url.auto.css']))  c::$data['url.auto.css']  = c::$data['url']  . '/assets/css/templates';
    if(!isset(c::$data['url.auto.js']))   c::$data['url.auto.js']   = c::$data['url']  . '/assets/js/templates';
    if(!isset(c::$data['root.auto.css'])) c::$data['root.auto.css'] = c::$data['root'] . DS . 'assets' . DS . 'css' . DS . 'templates';
    if(!isset(c::$data['root.auto.js']))  c::$data['root.auto.js']  = c::$data['root'] . DS . 'assets' . DS . 'js'  . DS . 'templates';

    // connect the url class with its handlers
    url::$home = c::$data['url'];
    url::$to   = c::$data['url.to'];

    // setup the thumbnail generator
    thumb::$defaults['root'] = isset(c::$data['thumb.cache.root']) ? c::$data['thumb.cache.root'] : c::$data['root.index'] . DS . 'thumbs';
    thumb::$defaults['url']  = isset(c::$data['thumb.cache.url'])  ? c::$data['thumb.cache.url']  : url::$home . '/thumbs';
    
    // return the entire config array
    return c::$data;

  }

  /**
   * Apply all locale settings and 
   * load language data   
   */
  static protected function localize() {

    // set the timezone for all date functions
    date_default_timezone_set(c::$data['timezone']);

    // set the local for the specific language
    setlocale(LC_ALL, static::$site->locale());          

    // additional language variables for multilang sites
    if(static::$site->multilang()) {
      // path for the language file
      $file = c::$data['root.site'] . DS . 'languages' . DS . static::$site->language()->code() . '.php';
      // load the file if it exists
      if(file_exists($file)) include_once($file);
    } 

  }

  /**
   * Loads all available plugins for the site
   * 
   * @return array
   */
  static protected function plugins() {
    
    // check for a cached plugins array
    if(!is_null(static::$plugins)) return static::$plugins;

    // check for an existing plugins dir
    if(!is_dir(c::$data['root.plugins'])) return static::$plugins = array();

    foreach(array_diff(scandir(c::$data['root.plugins']), array('.', '..')) as $file) {
      if(is_dir(c::$data['root.plugins'] . DS . $file)) static::plugin($file);      
    }

    return static::$plugins;

  }

  /**
   * Loads a single plugin
   * Can be used in other plugins to require 
   * a plugin, which is not yet loaded
   * 
   * @param string $name
   * @return mixed
   */
  static protected function plugin($name) {

    if(isset(static::$plugins[$name])) return true;

    $file = c::$data['root.plugins'] . DS . $name . DS . $name . '.php';
    
    if(file_exists($file)) return static::$plugins[$name] = include_once($file);

  }

  /**
   * Tries to find a controller for 
   * the current page and loads the data
   *
   * @return array
   */
  static protected function controller($page) {

    $file = c::$data['root.controllers'] . DS . $page->template() . '.php';

    if(file_exists($file)) {

      $callback = include_once($file);

      if(is_callable($callback)) return (array)call_user_func_array($callback, array(
        static::$site, 
        static::$site->children(), 
        $page
      ));

    } 

    return array();

  }

  /**
   * 
   */
  static protected function tags() {

    // load all kirby tags
    include_once(__DIR__ . DS . 'config'  . DS . 'tags.php');
    include_once(__DIR__ . DS . 'vendors' . DS . 'parsedown.php');

    // install additional kirby tags
    kirbytext::install(c::$data['root.tags']);

  }

  /**
   * Returns the HTML for a page without caching
   * 
   * @return string
   */
  static protected function render($page) {

    // load kirbytext and all tags
    static::tags();

    // localize
    static::localize();

    // load the plugins
    static::plugins();

    // setup and return the template
    return static::template($page);

  }

  /**
   * Template configuration
   */
  static protected function template($page) {

    // apply the basic template vars
    tpl::$data = array_merge(array(
      'site'  => static::$site,
      'pages' => static::$site->children(),
      'page'  => $page
    ), static::controller($page));

    return tpl::load($page->templateFile());

  }

  /**
   * Returns the HTML for a page with caching enabled
   * 
   * @return string
   */
  static protected function cache($page) {

    // set the cache location
    cache::$root = c::$data['root.cache'];

    // try to read the cache
    $cache = true ? cache::get($page->id()) : null;

    // fetch fresh content if the cache is empty
    if(empty($cache)) {
      $cache = static::render($page);
      cache::set($page->id(), $cache);
    }

    return $cache;

  }

  /**
   * Returns the site singleton
   * 
   * @return Site
   */
  static public function site() {
    return static::$site;
  }

  /**
   * Returns the currently active page
   * 
   * @return Page
   */
  static public function page() {
    return static::$page;
  }

}