<?php

use Kirby\Component;
use Kirby\ErrorHandling;
use Kirby\Event;
use Kirby\Registry;
use Kirby\Request;
use Kirby\Roots;
use Kirby\Urls;

class Kirby {

  static public $version = '2.5.10';
  static public $instance;
  static public $hooks = array();
  static public $triggered = array();

  public $roots;
  public $urls;
  public $cache;
  public $path;
  public $options = array();
  public $routes;
  public $router;
  public $route;
  public $site;
  public $page;
  public $plugins;
  public $response;
  public $request;
  public $components = [];
  public $registry;

  protected $configuring = false;

  static public function instance($class = null) {
    if(!is_null(static::$instance)) return static::$instance;
    return static::$instance = $class ? new $class : new static;
  }

  static public function version() {
    return static::$version;
  }

  public function __construct($options = array()) {

    $this->roots    = new Roots(dirname(__DIR__));
    $this->urls     = new Urls();
    $this->registry = new Registry($this);
    $this->options  = array_merge($this->defaults(), $options);
    $this->path     = implode('/', (array)url::fragments(detect::path()));

    // make sure the instance is stored / overwritten
    static::$instance = $this;

  }

  public function defaults() {

    $defaults = array(
      'url'                             => false,
      'timezone'                        => 'UTC',
      'license'                         => null,
      'rewrite'                         => true,
      'error'                           => 'error',
      'home'                            => 'home',
      'locale'                          => 'en_US.UTF8',
      'routes'                          => array(),
      'headers'                         => array(),
      'languages'                       => array(),
      'roles'                           => array(),
      'cache'                           => false,
      'debug'                           => 'env',
      'whoops'                          => true,
      'ssl'                             => false,
      'cache.driver'                    => 'file',
      'cache.options'                   => array(),
      'cache.ignore'                    => array(),
      'cache.autoupdate'                => true,
      'date.handler'                    => 'date',
      'kirbytext.video.class'           => 'video',
      'kirbytext.video.width'           => false,
      'kirbytext.video.height'          => false,
      'kirbytext.video.youtube.options' => array(),
      'kirbytext.video.vimeo.options'   => array(),
      'kirbytext.image.figure'          => true,
      'content.file.extension'          => 'txt',
      'content.file.ignore'             => array(),
      'content.file.normalize'          => false,
      'representations.accept'          => false,
      'email.service'                   => 'mail',
      'email.to'                        => null,
      'email.replyTo'                   => null,
      'email.subject'                   => null,
      'email.body'                      => null,
      'email.options'                   => array(),
    );

    return $defaults;

  }

  public function roots() {
    return $this->roots;
  }

  public function urls() {
    return $this->urls;
  }

  public function registry() {
    return $this->registry;
  }

  public function url($url = null) {
    return $this->urls->index($url);
  }

  public function options() {
    return $this->options;
  }

  public function option($key, $default = null) {
    return a::get($this->options, $key, $default);
  }

  public function path() {
    return $this->path;
  }

  public function page() {
    return $this->page;
  }

  public function response() {
    return $this->response;
  }

  /**
   * Install a new entry in the registry
   */
  public function set() {
    return call_user_func_array([$this->registry, 'set'], func_get_args());
  }

  /**
   * Retrieve an entry from the registry
   */
  public function get() {
    return call_user_func_array([$this->registry, 'get'], func_get_args());
  }

  public function configure() {

    // prevent loading configuration twice
    // this prevents issues if config is loaded indirectly from the config
    if($this->configuring) return;
    $this->configuring = true;

    // load all available config files
    $root    = $this->roots()->config();
    $configs = array(
      'main' => 'config.php',
      'host' => 'config.' . server::get('SERVER_NAME') . '.php',
      'addr' => 'config.' . server::get('SERVER_ADDR') . '.php',
    );

    $allowed = array_filter(dir::read($root), function($file) {
      return substr($file, 0, 7) === 'config.' and substr($file, -4) === '.php';
    });

    foreach($configs as $config) {
      $file = $root . DS . $config;
      if(in_array($config, $allowed, true) and file_exists($file)) include_once($file);
    }

    // apply the options
    $this->options = array_merge($this->options, c::$data);

    // overwrite the autodetected url
    if($url = $this->options['url']) {
      $this->url($url);
    }

    // connect the url class with its handlers
    url::$home = $this->urls()->index();
    url::$to   = $this->option('url.to', function($url = '', $lang = null) {

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
          if($page = page($url)) {
            // use the "official" page url
            return $page->url($lang);
          } else {
            // don't convert absolute urls
            return url::makeAbsolute($url);
          }
          break;
      }

    });

    // setup the pagination redirect to the error page
    pagination::$defaults['redirect'] = $this->option('error');

    // setting up the email class
    email::$defaults['service'] = $this->option('email.service');
    email::$defaults['from']    = $this->option('email.from');
    email::$defaults['to']      = $this->option('email.to');
    email::$defaults['replyTo'] = $this->option('email.replyTo');
    email::$defaults['subject'] = $this->option('email.subject');
    email::$defaults['body']    = $this->option('email.body');
    email::$defaults['options'] = $this->option('email.options');

  }

  /**
   * Registers all routes
   *
   * @param array $routes New routes
   * @return array
   */
  public function routes($routes = array()) {

    // extend the existing routes
    if(!empty($routes) and is_array($routes)) {
      return $this->options['routes'] = array_merge($this->options['routes'], $routes);
    }

    $routes = $this->options['routes'];
    $kirby  = $this;
    $site   = $this->site();

    // language detection function
    $langDetect = function() use($site, $kirby) {
      if(get('language') === 'switch') {
        // user comes from a different domain and wants to switch languages
        $language = $kirby->route->lang;
        s::set('kirby_language', $language->code());
      } else if(s::get('kirby_language') and $language = $site->sessionLanguage()) {
        // $language is already set but the user wants to
        // select another language
        $referer = r::referer();
        if(!empty($referer) && str::startsWith($referer, $this->urls()->index())) {
          $language = $kirby->route->lang;
        }
      } else {
        // detect the user language
        $language = $site->detectedLanguage();
      }

      // build language homepage URL including params and/or query
      $url = $language->url();
      if($params = url::params()) $url .= '/' . url::paramsToString($params);
      if($query  = url::query())  $url .= '/?' . url::queryToString($query);

      // redirect to the language homepage
      if($language && rtrim(url::current(), '/') !== rtrim($url, '/')) {
        go($url);
      }
    };

    // fallback route for both single and multilang branches
    $otherRoute = function($path = null) use($site, $kirby, $langDetect) {

      // handle language homepages if the language detector is activated
      if($kirby->option('language.detect') && $kirby->route->lang && (!$path || $path === '/') && $kirby->route->lang->isRoot()) {
        call($langDetect);
      }

      // get the language code from the route
      $lang = ($kirby->route->lang)? $kirby->route->lang->code() : false;

      // visit the currently active page
      $page = ($lang)? $site->visit($path, $lang) : $site->visit($path);

      // redirections for files and invalid representations
      if($site->representation !== null) {

        // get the filename
        $filename = rawurldecode(basename($path));
        $pagepath = dirname($path);

        // check if there's a page for the parent path
        if($parent = $site->find($pagepath)) {
          // check if there's a file for the last element of the path
          if($file = $parent->file($filename)) {
            return go($file->url());
          }
        }

        // prevent invalid representation routes
        if($site->representation === '' || $site->representation != $page->representation()) {
          return $site->errorPage();
        }

      }

      return $page;

    };

    // tinyurl handling
    $routes['tinyurl'] = $this->component('tinyurl')->route();

    // home redirect
    $routes['homeRedirect'] = array(
      'pattern' => $this->options['home'] . '(\..*)?',
      'action'  => function($extension = null) {
        // ignore invalid extensions
        if($extension === '.') $extension = '';

        redirect::send(url::build([
          'fragments' => ($extension)? [$extension] : null
        ]), 307);
      }
    );

    // plugin assets
    $routes['pluginAssets'] = array(
      'pattern' => 'assets/plugins/(:any)/(:all)',
      'method'  => 'GET',
      'action'  => function($plugin, $path) use($kirby) {
        $errorResponse = new Response('The file could not be found', 'txt', 404);

        // filter out plugin names that contain directory traversal attacks
        if(preg_match('{[\\\\/]}', urldecode($plugin))) return $errorResponse;
        if(preg_match('{^[.]+$}', $plugin))             return $errorResponse;

        // build the path to the requested file
        $pluginRoot = $kirby->roots()->plugins() . DS . $plugin . DS . 'assets';
        $fileRoot   = $pluginRoot . DS . str_replace('/', DS, $path);
        if(!is_file($fileRoot)) return $errorResponse;

        // make sure that we are still in the plugin's asset dir
        if(!str::startsWith(realpath($fileRoot), realpath($pluginRoot))) return $errorResponse;

        // success, serve the file
        return new Response(f::read($fileRoot), f::extension($fileRoot));
      }
    );

    // all other urls
    if($site->multilang()) {

      // first register all languages that are not at the root of the domain
      // otherwise they would capture all requests
      foreach($site->languages()->sortBy('isRoot', 'asc') as $lang) {
        $pattern = ($lang->path())? $lang->path() . '/(:all?)' : '(:all)';
        $routes[] = array(
          'pattern' => $pattern,
          'host'    => $lang->host(),
          'method'  => 'ALL',
          'lang'    => $lang,
          'action'  => $otherRoute
        );
      }

      // fallback if no language is at the root
      $routes['others'] = array(
        'pattern' => '(.*)', // this can't be (:all) to avoid overriding the actual language route
        'method'  => 'ALL',
        'action'  => function($uri) use($site, $kirby, $langDetect) {
          if($uri && $uri !== '/') {
            // first try to find a page with the given URI
            $page = page($uri);
            if($page) return go($page);

            // the URI is not a valid page -> error page
            return $site->errorPage();
          } else {
            // no URI is given

            // handle language homepages if the language detector is activated
            if($kirby->option('language.detect')) {
              call($langDetect);
            }

            // otherwise redirect to the homepage of the default language
            return go($site->defaultLanguage()->url());
          }
        }
      );

    } else {

      // all other urls for single-language installations
      $routes['others'] = array(
        'pattern' => '(:all)',
        'method'  => 'ALL',
        'lang'    => false,
        'action'  => $otherRoute
      );

    }

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
   * Loads a single plugin
   *
   * @param string $name
   * @param string $mode
   * @return mixed
   */
  public function plugin($name, $mode = 'dir') {

    if(isset($this->plugins[$name])) return $this->plugins[$name];

    if($mode == 'dir') {
      $file = $this->roots->plugins() . DS . $name . DS . $name . '.php';
    } else {
      $file = $this->roots->plugins() . DS . $name . '.php';
    }

    // make the kirby variable available in plugin files
    $kirby = $this;

    if(file_exists($file)) return $this->plugins[$name] = include_once($file);

    return false;

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

  }

  /**
   * Autoloads all page models
   */
  public function models() {

    if(!is_dir($this->roots()->models())) return false;

    $root  = $this->roots()->models();
    $files = dir::read($root);
    $load  = array();

    foreach($files as $file) {
      if(f::extension($file) != 'php') continue;
      $name      = f::name($file);
      $classname = str_replace(array('.', '-', '_'), '', $name . 'page');
      $load[$classname] = $root . DS . $file;

      // register the model
      page::$models[$name] = $classname;
    }

    // start the autoloader
    if(!empty($load)) {
      load($load);
    }

  }

  public function localize() {

    $site = $this->site();

    if($site->multilang() and !$site->language()) {
      $site->language = $site->languages()->findDefault();
    }

    // set the local for the specific language
    if(is_array($site->locale())) {
      foreach($site->locale() as $key => $value) {
        setlocale($key, $value);
      }
    } else {
      setlocale(LC_ALL, $site->locale());
    }

    // additional language variables for multilang sites
    if($site->multilang()) {
      // path for the language file
      $path = $this->roots()->languages() . DS . $site->language()->code();

      // load .php file if it exists
      if(f::exists($path . '.php')) include_once($path . '.php');

      // load .yml file and set as language variables if it exists
      if(f::exists($path . '.yml')) l::set(data::read($path . '.yml', 'yaml'));
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

    // check for an existing site directory
    if(!is_dir($this->roots()->site())) {
      trigger_error('The site directory is missing', E_USER_ERROR);
    }

    // check for an existing content directory
    if(!is_dir($this->roots()->content())) {
      trigger_error('The content directory is missing', E_USER_ERROR);
    }

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

    // configure pagination urls
    $query  = (string)$this->request()->query();
    $params = (string)$this->request()->params() . r($query, '?') . $query;

    pagination::$defaults['url'] = $page->url() . r($params, '/') . $params;

    // cache the result if possible
    if($this->options['cache'] && $page->isCachable() && in_array(r::method(), ['GET', 'HEAD'])) {

      // try to read the cache by cid (cache id)
      $cacheId = md5(url::current() . $page->representation());

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
    return $this->component('template')->render($page, $data);
  }

  public function request() {
    if(!is_null($this->request)) return $this->request;
    return $this->request = new Request($this);
  }

  public function router() {
    return $this->router;
  }

  public function route() {
    return $this->route;
  }

  /**
   * Starts the router, renders the page and returns the response
   *
   * @return mixed
   */
  public function launch() {

    // this will trigger the configuration
    $site = $this->site();

    // start the error handler
    new ErrorHandling($this);

    // force secure connections if enabled
    if($this->option('ssl') and !r::secure()) {
      // rebuild the current url with https
      go(url::build(['scheme' => 'https']), 301);
    }

    // set the timezone for all date functions
    date_default_timezone_set($this->options['timezone']);

    // load all extensions
    $this->extensions();

    // load all models
    $this->models();

    // load all plugins
    $this->plugins();

    // start the router
    $this->router = new Router($this->routes());
    $this->route  = $this->router->run(trim($this->path(), '/'));

    // check for a valid route
    if(is_null($this->route)) {
      header::status('500');
      header::type('json');
      die(json_encode(array(
        'status'  => 'error',
        'message' => 'Invalid route or request method'
      )));
    }

    // call the router action with all arguments from the pattern
    $response = call($this->route->action(), $this->route->arguments());

    // load all language variables
    // this can only be loaded once the router action has been called
    // otherwise the current language is not yet available
    $this->localize();

    // build the response
    $this->response = $this->component('response')->make($response);

    // store the current language in the session
    if(
        $this->option('language.detect') &&
        $this->site()->multilang() &&
        $this->site()->language()
      ) {
      s::set('kirby_language', $this->site()->language()->code());
    }

    return $this->response;

  }

  /**
   * Register a new hook
   *
   * @param string/array $hook The name of the hook
   * @param closure $callback
   */
  public function hook($hook, $callback) {

    if(is_array($hook)) {
      foreach($hook as $h) $this->hook($h, $callback);
      return;
    }

    if(isset(static::$hooks[$hook]) and is_array(static::$hooks[$hook])) {
      static::$hooks[$hook][] = $callback;
    } else {
      static::$hooks[$hook] = array($callback);
    }

  }

  /**
   * Trigger a hook
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the hook
   * @return mixed
   */
  public function trigger($event, $args = null) {

    if(is_string($event)) {
      $hook = $event;
      $event = new Event($hook);
    } else if(is_a($event, 'Kirby\\Event')) {
      $hook = $event->type();
    } else {
      throw new Error('Invalid event.');
    }

    foreach(static::$hooks as $pattern => $hooks) {
      if(!is_array($hooks)) continue;
      if(!fnmatch($pattern, $hook)) continue;

      foreach($hooks as $key => $callback) {
        if(!array_key_exists($pattern, static::$triggered)) static::$triggered[$pattern] = array();
        if(in_array($key, static::$triggered[$pattern])) continue;

        static::$triggered[$pattern][] = $key;

        // make sure that we always have a Closure object
        if(is_string($callback)) {
          $callback = (new ReflectionFunction($callback))->getClosure();
        }

        try {
          $callback = $callback->bindTo($event);
          call($callback, $args);
        } catch(Exception $e) {
          // caught callback error
        }
      }
    }
  }

  static public function start() {
    return kirby()->launch();
  }

  /**
   * Register and fetch core components
   */
  public function component($name, $component = null) {
    if(is_null($component)) {
      if(!isset($this->components[$name])) {
        // load the default component if it exists
        if(file_exists(__DIR__ . DS . 'kirby' . DS . 'component' . DS . strtolower($name) . '.php')) {
          $this->component($name, 'Kirby\\Component\\' . $name);
        } else {
          throw new Exception('The component "' . $name . '" does not exist');
        }
      }
      return $this->components[$name];
    } else {

      if(!is_string($component)) {
        throw new Exception('Please provide a valid component name');
      }

      // init the component
      $object = new $component($this);

      if(!is_a($object, 'Kirby\\Component')) {
        throw new Exception('The component "' . $name . '" must be an instance of the Kirby\\Component class');
      }

      if(!is_a($object, 'Kirby\\Component\\' . $name)) {
        throw new Exception('The component "' . $name . '" must be an instance of the Kirby\\Component\\' . ucfirst($name) . ' class');
      }

      // add the component defaults
      $this->options = array_merge($object->defaults(), $this->options);

      // configure the component
      $object->configure();

      // register the component
      $this->components[$name] = $object;

    }
  }

  /**
   * Improved var_dump() output
   */
  public function __debuginfo() {
    return [
      'version'    => $this->version(),
      'request'    => $this->request(),
      'site'       => $this->site(),
      'urls'       => $this->urls(),
      'roots'      => $this->roots(),
      'options'    => $this->options(),
      'components' => array_keys((array)$this->components),
      'plugins'    => array_keys((array)$this->plugins),
      'hooks'      => array_keys((array)static::$hooks),
      'routes'     => array_values(array_map(function($route) {
        return $route['pattern'];
      }, $this->routes())),
    ];
  }

}
