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
  static public $site;

  // The currently active page
  static public $page;

  // The router object
  static public $router;

  // The current route object
  static public $route;

  // An array with all loaded plugins
  static public $plugins;

  static public function setup($config = array()) {

    // load all config settings
    static::configure($config);

    // load the cms branch
    static::branch();

    // create a new site object
    static::$site = $GLOBALS['site'] = new Site(c::$data);

    // start the router
    static::$router = new Router();

    // register all available
    static::$router->register(static::routes());

    // only use the fragments of the path without params
    static::$route = static::$router->run(static::path());

    // load kirbytext and all tags
    static::tags();

    // load the plugins
    static::plugins();

    // return the configured site object
    return static::$site;

  }

  /**
   * The path which will be used for the router
   *
   * @return string
   */
  static public function path() {
    return implode('/', (array)url::fragments(detect::path()));
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

    if(is_null(static::$site) or !empty($config)) static::setup($config);

    call(static::$route->action(), static::$route->arguments());

    return static::render(static::$page);

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
        kirby::$page = kirby::$site->visit($path);
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
      if(r::cli()) {
        return c::$data['url'] = '/';
      } else {
        return c::$data['url'] = url::scheme() . '://' . server::get('HTTP_HOST') . preg_replace('!\/index\.php$!i', '', server::get('SCRIPT_NAME'));
      }
    } else {
      return c::$data['url'];
    }
  }

  /**
   * Sets all defaults and loads the user configuration
   *
   * @param array $config
   */
  static protected function configure($config = array()) {

    // start with a fresh configuration
    c::$data = array();

    // set some defaults
    c::$data['root']         = dirname(__DIR__);
    c::$data['root.kirby']   = __DIR__;
    c::$data['root.content'] = c::$data['root'] . DS . 'content';
    c::$data['root.site']    = c::$data['root'] . DS . 'site';

    // the default timezone
    c::$data['timezone'] = 'UTC';

    // disable the cache by default
    c::$data['cache'] = false;

    // set the default license code
    c::$data['license'] = null;

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

    // auto css and js setup
    c::$data['auto.css.url']  = 'assets/css/templates';
    c::$data['auto.css.root'] = c::$data['root'] . DS . 'assets' . DS . 'css' . DS . 'templates';
    c::$data['auto.js.url']   = 'assets/js/templates';
    c::$data['auto.js.root']  = c::$data['root'] . DS . 'assets' . DS . 'js'  . DS . 'templates';

    // load the user config
    if(file_exists(c::$data['root.config'] . DS . 'config.php')) include(c::$data['root.config'] . DS . 'config.php');

    // pass the config vars from the constructor again to overwrite
    // stuff from the user config
    c::$data = array_merge(c::$data, $config);

    // detect and store the url
    static::url();

    // default url handler
    if(empty(c::$data['url.to'])) {
      c::$data['url.to'] = function($url = '') {
        // don't convert absolute urls
        return url::isAbsolute($url) ? $url : url::makeAbsolute($url);
      };
    }

    // connect the url class with its handlers
    url::$home = c::$data['url'];
    url::$to   = c::$data['url.to'];

    // setup the thumbnail generator
    thumb::$defaults['root']     = isset(c::$data['thumb.root'])     ? c::$data['thumb.root']     : c::$data['root'] . DS . 'thumbs';
    thumb::$defaults['url']      = isset(c::$data['thumb.url'])      ? c::$data['thumb.url']      : 'thumbs';
    thumb::$defaults['driver']   = isset(c::$data['thumb.driver'])   ? c::$data['thumb.driver']   : 'gd';
    thumb::$defaults['filename'] = isset(c::$data['thumb.filename']) ? c::$data['thumb.filename'] : '{safeName}-{hash}.{extension}';

    // build absolute urls
    c::$data['auto.css.url'] = url::makeAbsolute(c::$data['auto.css.url'], url::$home);
    c::$data['auto.js.url']  = url::makeAbsolute(c::$data['auto.js.url'], url::$home);

    thumb::$defaults['url']  = url::makeAbsolute(thumb::$defaults['url'], url::$home);

    if(c::$data['cache']) {
      // TODO: make this switchable
      cache::setup('file', c::get('root.cache'));
    } else {
      cache::setup('mock');
    }

    // set the timezone for all date functions
    date_default_timezone_set(c::$data['timezone']);

    // return the entire config array
    return c::$data;

  }

  /**
   * Apply all locale settings and
   * load language data
   */
  static protected function localize() {

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

  static protected function tags() {

    // load all kirby tags
    include_once(__DIR__ . DS . 'config'  . DS . 'tags.php');
    include_once(__DIR__ . DS . 'vendors' . DS . 'parsedown.php');

    // install additional kirby tags
    kirbytext::install(c::$data['root.tags']);

  }

  /**
   * Renders the HTML for the page or fetches it from the cache
   *
   * @param Page $page
   * @param boolean $headers
   * @return string
   */
  static public function render(Page $page, $headers = true) {

    // send all headers for the page
    if($headers) $page->headers();

    // load all language variables
    static::localize();

    // if the cache is activated…
    if(c::$data['cache']) {
      // return the page from cache
      return static::cache($page);
    } else {
      // render the template
      return static::template($page);
    }

  }

  /**
   * Template configuration
   */
  static protected function template(Page $page) {

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
  static protected function cache(Page $page) {

    // TODO: check for site modification date and flush the cache

    // try to read the cache
    $cache = true ? cache::get($page->id()) : null;

    // fetch fresh content if the cache is empty
    if(empty($cache)) {
      $cache = static::template($page);
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