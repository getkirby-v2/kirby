<?php

use Kirby\Roots;
use Kirby\Urls;
use Kirby\Request;

class Kirby extends Obj {

  static public $version = '2.0.0';
  static public $instance;

  public $roots;
  public $urls;
  public $cache;
  public $path;
  public $options = array();
  public $license;
  public $routes;
  public $router;
  public $site;
  public $page;
  public $plugins;
  public $response;
  public $request;

  static public function instance($class = null) {
    if(!is_null(static::$instance)) return static::$instance;
    return static::$instance = $class ? new $class : new static;
  }

  static public function version() {
    return static::$version;
  }

  public function __construct() {
    $this->roots   = new Roots(dirname(__DIR__));
    $this->urls    = new Urls();
    $this->options = $this->defaults();
    $this->rewrite = false;
    $this->path    = implode('/', (array)url::fragments(detect::path()));

    // make sure the instance is stored / overwritten
    static::$instance = $this;
  }

  public function defaults() {
    return array(
      'timezone'               => 'UTC',
      'license'                => null,
      'rewrite'                => true,
      'error'                  => 'error',
      'home'                   => 'home',
      'locale'                 => 'en_US.UTF8',
      'routes'                 => array(),
      'headers'                => array(),
      'languages'              => array(),
      'roles'                  => array(),
      'cache'                  => false,
      'debug'                  => false,
      'cache.driver'           => 'file',
      'cache.options'          => array(),
      'cache.ignore'           => array(),
      'cache.autoupdate'       => true,
      'tinyurl.enabled'        => true,
      'tinyurl.folder'         => 'x',
      'markdown.extra'         => false,
      'markdown.breaks'        => true,
      'smartypants'            => false,
      'kirbytext.video.class'  => 'video',
      'kirbytext.video.width'  => false,
      'kirbytext.video.height' => false,
      'content.file.extension' => 'txt',
      'content.file.ignore'    => array(),
      'thumbs.driver'          => 'gd',
      'thumbs.filename'        => '{safeName}-{hash}.{extension}',
    );
  }

  public function option($key, $default = null) {
    return a::get($this->options, $key, $default);
  }

  public function path() {
    return $this->path;
  }

  public function url() {
    return $this->urls->index();
  }

  public function configure() {

    // load all available config files
    $root    = $this->roots()->config();
    $configs = array(
      'main' => $root . DS . 'config.php',
      'host' => $root . DS . 'config.' . server::get('HTTP_HOST') . '.php',
      'addr' => $root . DS . 'config.' . server::get('SERVER_ADDR') . '.php',
    );

    foreach($configs as $config) {
      if(file_exists($config)) include_once($config);
    }

    // apply the options
    $this->options = array_merge($this->options, c::$data);

    // connect the url class with its handlers
    url::$home = $this->urls()->index();
    url::$to   = $this->option('url.to', function($url = '') {

      if(url::isAbsolute($url)) return $url;

      $start = substr($url, 0, 1);
      switch($start) {
        case '#':
          return $url;
          break;
        case '.':
          return page()->url() . '/' . $url;
          break;
        default:
          // don't convert absolute urls
          return url::makeAbsolute($url);
          break;
      }

    });

    // setup the thumbnail generator
    thumb::$defaults['root']     = $this->roots->thumbs();
    thumb::$defaults['url']      = $this->urls->thumbs();
    thumb::$defaults['driver']   = $this->option('thumbs.driver');
    thumb::$defaults['filename'] = $this->option('thumbs.filename');

  }

  /**
   * Registers all routes
   *
   * @return array
   */
  public function routes() {

    $routes = $this->options['routes'];
    $kirby  = $this;
    $site   = $this->site();

    if($site->multilang()) {

      // language resolver
      $routes['languages'] = array(
        'pattern' => '(' . implode('|', $site->languages()->codes()) . ')/(:all?)',
        'method'  => 'ALL',
        'action'  => function($lang, $path = null) use($site) {
          // visit the currently active page for a specific language
          return $site->visit($path, $lang);
        }
      );

    }

    // tinyurl handling
    if($this->options['tinyurl.enabled']) {
      $routes['tinyurl'] = array(
        'pattern' => $this->options['tinyurl.folder'] . '/(:any)/(:any?)',
        'action'  => function($hash, $lang = null) use($site) {
          $page = $site->index()->findBy('hash', $hash);
          if(!$page) return $site->errorPage();
          go($page->url($lang));
        }
      );
    }

    // all other urls
    $routes['others'] = array(
      'pattern' => '(:all)',
      'method'  => 'ALL',
      'action'  => function($path = null) use($site) {
        // visit the currently active page
        $page = $site->visit($path);

        // react on errors for invalid URLs
        if($page->isErrorPage() and $page->uri() != $path) {

          // get the filename
          $filename = basename($path);
          $pagepath = dirname($path);

          // check if there's a page for the parent path
          if($page = $site->find($pagepath)) {
            // check if there's a file for the last element of the path
            if($file = $page->file($filename)) {
              // TODO: put asset pipe here
              // redirect to the real file url to make this snappy
              go($file->url());
            }
          }

          // return the error page if there's no such page
          return $site->errorPage();

        }

        return $page;

      }

    );

    return $routes;

  }

  /**
   * Loads all available plugins for the site
   *
   * @return array
   */
  public function plugins() {

    // check for a cached plugins array
    if(!is_null($this->plugins)) return $this->plugins;

    // get the plugins root
    $root = $this->roots->plugins();

    // start the plugin registry
    $this->plugins = array();

    // check for an existing plugins dir
    if(!is_dir($root)) return $this->plugins;

    foreach(array_diff(scandir($root), array('.', '..')) as $file) {
      if(is_dir($root . DS . $file)) {
        $this->plugin($file, 'dir');
      } else if(f::extension($file) == 'php') {
        $this->plugin(f::name($file), 'file');
      }
    }

    return $this->plugins;

  }

  /**
   * Load all default extensions
   */
  public function extensions() {

    // load all kirby tags and field methods
    include_once(__DIR__ . DS . 'extensions' . DS . 'tags.php');
    include_once(__DIR__ . DS . 'extensions' . DS . 'methods.php');

    // install additional kirby tags
    kirbytext::install($this->roots->tags());

    // install the smartypants class if enabled
    if($this->options['smartypants']) {
      include_once(__DIR__ . DS . 'vendors' . DS . 'smartypants.php');
    }

  }

  /**
   * Loads a single plugin
   *
   * @param string $name
   * @param string $mode
   * @return mixed
   */
  public function plugin($name, $mode = 'dir') {

    if(isset($this->plugins[$name])) return true;

    if($mode == 'dir') {
      $file = $this->roots->plugins() . DS . $name . DS . $name . '.php';
    } else {
      $file = $this->roots->plugins() . DS . $name . '.php';
    }

    if(file_exists($file)) return $this->plugins[$name] = include_once($file);

  }

  /**
   * Tries to find a controller for
   * the current page and loads the data
   *
   * @return array
   */
  public function controller($page, $arguments = array()) {

    $file = $this->roots->controllers() . DS . $page->template() . '.php';

    if(file_exists($file)) {

      $callback = include_once($file);

      if(is_callable($callback)) return (array)call_user_func_array($callback, array(
        $this->site(),
        $this->site()->children(),
        $page,
        $arguments
      ));

    }

    return array();

  }

  public function localize() {

    // set the local for the specific language
    setlocale(LC_ALL, $this->site()->locale());

    // additional language variables for multilang sites
    if($this->site()->multilang()) {
      // path for the language file
      $file = $this->roots()->languages() . DS . $this->site()->language()->code() . '.php';
      // load the file if it exists
      if(file_exists($file)) include_once($file);
    }

  }

  /**
   * Returns the branch file
   *
   * @return string
   */
  public function branch() {

    // which branch?
    $branch = count($this->options['languages']) > 0 ? 'multilang' : 'default';

    // build the path for the branch file
    return __DIR__ . DS . 'branches' . DS . $branch . '.php';

  }

  /**
   * Initializes and returns the site object
   * depending on the appropriate branch
   *
   * @return Site
   */
  public function site() {

    // check for a cached version of the site object
    if(!is_null($this->site)) return $this->site;

    // load all options
    $this->configure();

    // setup the cache
    $this->cache();

    // load the main branch file
    include_once($this->branch());

    // create the site object
    return $this->site = new Site($this);

  }

  /**
   * Cache setup
   *
   * @return Cache
   */
  public function cache() {

    if(!is_null($this->cache)) return $this->cache;

    // cache setup
    if($this->options['cache']) {
      if($this->options['cache.driver'] == 'file' and empty($this->options['cache.options'])) {
        $this->options['cache.options'] = array(
          'root' => $this->roots()->cache()
        );
      }
      return $this->cache = cache::setup($this->options['cache.driver'], $this->options['cache.options']);
    } else {
      return $this->cache = cache::setup('mock');
    }

  }

  /**
   * Renders the HTML for the page or fetches it from the cache
   *
   * @param Page $page
   * @param boolean $headers
   * @return string
   */
  public function render(Page $page, $data = array(), $headers = true) {

    // register the currently rendered page
    $this->page = $page;

    // send all headers for the page
    if($headers) $page->headers();

    // cache the result if possible
    if($this->options['cache'] and $page->isCachable()) {

      // try to read the cache by cid (cache id)
      $cacheId = $page->cacheId();

      // check for modified content within the content folder
      // and auto-expire the page cache in such a case
      if($this->options['cache.autoupdate'] and $this->cache()->exists($cacheId)) {

        // get the creation date of the cache file
        $created = $this->cache()->created($cacheId);

        // make sure to kill the cache if the site has been modified
        if($this->site->wasModifiedAfter($created)) {
          $this->cache()->remove($cacheId);
        }

      }

      // try to fetch the template from cache
      $template = $this->cache()->get($cacheId);

      // fetch fresh content if the cache is empty
      if(empty($template)) {
        $template = $this->template($page, $data);
        // store the result for the next round
        $this->cache()->set($cacheId, $template);
      }

      return $template;

    }

    // return a fresh template
    return $this->template($page, $data);

  }

  /**
   * Template configuration
   *
   * @param Page $page
   * @param array $data
   * @return string
   */
  public function template(Page $page, $data = array()) {

    // set the timezone for all date functions
    date_default_timezone_set($this->options['timezone']);

    // load all language variables
    $this->localize();

    // load all extensions
    $this->extensions();

    // load all plugins
    $this->plugins();

    // apply the basic template vars
    tpl::$data = array_merge(array(
      'kirby' => $this,
      'site'  => $this->site(),
      'pages' => $this->site()->children(),
      'page'  => $page
    ), $data, $this->controller($page, $data));

    return tpl::load($page->templateFile());

  }

  public function request() {
    if(!is_null($this->request)) return $this->request;
    return $this->request = new Request($this);
  }

  public function response() {

    // this will trigger the configuration
    $site   = $this->site();
    $router = new Router($this->routes());
    $route  = $router->run($this->path());

    // check for a valid route
    if(is_null($route)) {
      header::status('500');
      header::type('json');
      die(json_encode(array(
        'status'  => 'error',
        'message' => 'Invalid route or request method'
      )));
    }

    $response = call($route->action(), $route->arguments());

    if(is_string($response)) {
      $this->response = static::render(page($response));
    } else if(is_array($response)) {
      $this->response = static::render(page($response[0]), $response[1]);
    } else if(is_a($response, 'Response')) {
      $this->response = $response;
    } else if(is_a($response, 'Page')) {
      $this->response = static::render($response);
    } else {
      $this->response = null;
    }

    return $this->response;

  }

  /**
   * Starts the router, renders the page and returns the response
   *
   * @return mixed
   */
  public function launch() {
    return $this->response();
  }

  static public function start() {
    return kirby()->launch();
  }

}