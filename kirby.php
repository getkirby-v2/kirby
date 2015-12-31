<?php

use Kirby\Roots;
use Kirby\Urls;
use Kirby\Request;

class Kirby extends Obj {

  static public $version = '2.2.3';
  static public $instance;
  static public $hooks = array();

  public $roots;
  public $urls;
  public $cache;
  public $path;
  public $options = array();
  public $license;
  public $routes;
  public $router;
  public $route;
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

  public function __construct($options = array()) {
    $this->roots   = new Roots(dirname(__DIR__));
    $this->urls    = new Urls();
    $this->options = array_merge($this->defaults(), $options);
    $this->path    = implode('/', (array)url::fragments(detect::path()));

    // make sure the instance is stored / overwritten
    static::$instance = $this;
  }

  public function defaults() {

    $defaults = array(
      'url'                           => false,
      'timezone'                      => 'UTC',
      'license'                       => null,
      'rewrite'                       => true,
      'error'                         => 'error',
      'home'                          => 'home',
      'locale'                        => 'en_US.UTF8',
      'routes'                        => array(),
      'headers'                       => array(),
      'languages'                     => array(),
      'roles'                         => array(),
      'cache'                         => false,
      'debug'                         => 'env',
      'ssl'                           => false,
      'cache.driver'                  => 'file',
      'cache.options'                 => array(),
      'cache.ignore'                  => array(),
      'cache.autoupdate'              => true,
      'date.handler'                  => 'date',
      'tinyurl.enabled'               => true,
      'tinyurl.folder'                => 'x',
      'markdown'                      => true,
      'markdown.extra'                => false,
      'markdown.breaks'               => true,
      'smartypants'                   => false,
      'smartypants.attr'              => 1,
      'smartypants.doublequote.open'  => '&#8220;',
      'smartypants.doublequote.close' => '&#8221;',
      'smartypants.space.emdash'      => ' ',
      'smartypants.space.endash'      => ' ',
      'smartypants.space.colon'       => '&#160;',
      'smartypants.space.semicolon'   => '&#160;',
      'smartypants.space.marks'       => '&#160;',
      'smartypants.space.frenchquote' => '&#160;',
      'smartypants.space.thousand'    => '&#160;',
      'smartypants.space.unit'        => '&#160;',
      'smartypants.skip'              => 'pre|code|kbd|script|style|math',
      'kirbytext.video.class'         => 'video',
      'kirbytext.video.width'         => false,
      'kirbytext.video.height'        => false,
      'kirbytext.image.figure'        => true,
      'content.file.extension'        => 'txt',
      'content.file.ignore'           => array(),
      'content.file.normalize'        => false,
      'thumbs.driver'                 => 'gd',
      'thumbs.filename'               => '{safeName}-{hash}.{extension}',
      'thumbs.destination'            => false,
      'email.service'                 => 'mail',
      'email.to'                      => null,
      'email.replyTo'                 => null,
      'email.subject'                 => null,
      'email.body'                    => null,
      'email.options'                 => array(),
    );

    // default markdown parser callback
    $defaults['markdown.parser'] = function($text) {

      // initialize the right markdown class
      $parsedown = kirby::instance()->option('markdown.extra') ? new ParsedownExtra() : new Parsedown();

      // set markdown auto-breaks
      $parsedown->setBreaksEnabled(kirby::instance()->option('markdown.breaks'));

      // parse it!
      return $parsedown->text($text);

    };

    // default smartypants parser callback
    $defaults['smartypants.parser'] = function($text) {
      $parser = new SmartyPantsTypographer_Parser(kirby::instance()->option('smartypants.attr', 1));
      return $parser->transform($text);
    };

    // css handler
    $defaults['css.handler'] = function($url, $media = null) {

      $kirby = kirby::instance();

      if(is_array($url)) {
        $css = array();
        foreach($url as $u) $css[] = call($kirby->option('css.handler'), array($u, $media));
        return implode(PHP_EOL, $css) . PHP_EOL;
      }

      // auto template css files
      if($url == '@auto') {

        $file = $kirby->site()->page()->template() . '.css';
        $root = $kirby->roots()->autocss() . DS . $file;
        $url  = $kirby->urls()->autocss() . '/' . $file;

        if(!file_exists($root)) return false;

      }

      return html::tag('link', null, array(
        'rel'   => 'stylesheet',
        'href'  => url($url),
        'media' => $media
      ));

    };

    // js handler
    $defaults['js.handler'] = function($src, $async = false) {

      $kirby = kirby::instance();

      if(is_array($src)) {
        $js = array();
        foreach($src as $s) $js[] = call($kirby->option('js.handler'), array($s, $async));
        return implode(PHP_EOL, $js) . PHP_EOL;
      }

      // auto template css files
      if($src == '@auto') {

        $file = $kirby->site()->page()->template() . '.js';
        $root = $kirby->roots()->autojs() . DS . $file;
        $src  = $kirby->urls()->autojs() . '/' . $file;

        if(!file_exists($root)) return false;

      }

      return html::tag('script', '', array(
        'src'   => url($src),
        'async' => $async
      ));

    };

    return $defaults;

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
    if($this->options['url']) {
      $this->urls->index = $this->options['url'];
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

    // setup the thumbnail generator
    thumb::$defaults['root']        = $this->roots->thumbs();
    thumb::$defaults['url']         = $this->urls->thumbs();
    thumb::$defaults['driver']      = $this->option('thumbs.driver');
    thumb::$defaults['filename']    = $this->option('thumbs.filename');
    thumb::$defaults['destination'] = $this->option('thumbs.destination');

    // setting up the email class
    email::$defaults['service'] = $this->option('email.service');
    email::$defaults['from']    = $this->option('email.from');
    email::$defaults['to']      = $this->option('email.to');
    email::$defaults['replyTo'] = $this->option('email.replyTo');
    email::$defaults['subject'] = $this->option('email.subject');
    email::$defaults['body']    = $this->option('email.body');
    email::$defaults['options'] = $this->option('email.options');

    // simple error handling
    if($this->options['debug'] === true) {
      error_reporting(E_ALL);
      ini_set('display_errors', 1);
    } else if($this->options['debug'] === false) {
      error_reporting(0);
      ini_set('display_errors', 0);
    }

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

    if($site->multilang()) {

      foreach($site->languages() as $lang) {

        $routes[] = array(
          'pattern' => ltrim($lang->url . '/(:all?)', '/'),
          'method'  => 'ALL',
          'lang'    => $lang,
          'action'  => function($path = null) use($kirby, $site) {
            return $site->visit($path, $kirby->route->lang->code());
          }
        );

      }

      // fallback for the homepage
      $routes[] = array(
        'pattern' => '/',
        'method'  => 'ALL',
        'action'  => function() use($kirby, $site) {

          // check if the language detector is activated
          if($kirby->option('language.detect')) {

            if(s::get('language') and $language = $kirby->site()->sessionLanguage()) {
              // $language is already set but the user wants to 
              // select the default language
              $referer = r::referer();
              if(!empty($referer) && str::startsWith($referer, $this->urls()->index())) {
                $language = $kirby->site()->defaultLanguage();
              } 
            } else {
              // detect the user language
              $language = $kirby->site()->detectedLanguage();
            }

          } else {
            // always use the default language if the detector is disabled
            $language = $kirby->site()->defaultLanguage();
          }

          // redirect to the language homepage if necessary
          if($language->url != '/' and $language->url != '') {
            go($language->url());
          }

          // plain home pages
          return $site->visit('/', $language->code());

        }
      );

    }

    // tinyurl handling
    if($this->options['tinyurl.enabled']) {
      $routes['tinyurl'] = array(
        'pattern' => $this->options['tinyurl.folder'] . '/(:any)/(:any?)',
        'action'  => function($hash, $lang = null) use($site) {          
          // make sure the language is set
          $site->visit('/', $lang);
          // find the page by it's tiny hash
          if($page = $site->index()->findBy('hash', $hash)) {
            go($page->url($lang));            
          } else {
            return $site->errorPage();            
          }
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
      $file = $this->roots()->languages() . DS . $site->language()->code() . '.php';
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

    // configure pagination urls
    $query  = (string)$this->request()->query();
    $params = (string)$this->request()->params() . r($query, '?') . $query;

    pagination::$defaults['url'] = $page->url() . r($params, '/') . $params;

    // cache the result if possible
    if($this->options['cache'] and $page->isCachable()) {

      // try to read the cache by cid (cache id)
      $cacheId = md5(url::current());

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

    // apply the basic template vars
    tpl::$data = array_merge(tpl::$data, array(
      'kirby' => $this,
      'site'  => $this->site(),
      'pages' => $this->site()->children(),
      'page'  => $page
    ), $page->templateData(), $data, $this->controller($page, $data));

    if(!file_exists($page->templateFile())) {
      throw new Exception('The default template could not be found');
    }

    return tpl::load($page->templateFile());

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

    // force secure connections if enabled
    if($this->option('ssl') and !r::secure()) {
      // rebuild the current url with https
      go(url::build(array('scheme' => 'https')));
    }

    // set the timezone for all date functions
    date_default_timezone_set($this->options['timezone']);

    // load all extensions
    $this->extensions();

    // load all plugins
    $this->plugins();

    // load all models
    $this->models();

    // start the router
    $this->router = new Router($this->routes());
    $this->route  = $this->router->run($this->path());

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

    // work with the response
    if(is_string($response)) {
      $page = page($response);
      $this->response = static::render($page);
    } else if(is_array($response)) {
      $page = page($response[0]);
      $this->response = static::render($page, $response[1]);
    } else if(is_a($response, 'Page')) {
      $page = $response;
      $this->response = static::render($page);      
    } else if(is_a($response, 'Response')) {
      $page = null;
      $this->response = $response;
    } else {
      $page = null;
      $this->response = null;
    }

    if($this->site()->multilang() and $language = $this->site()->language()) {
      // store the current language in the session
      s::set('language', $language->code());
    }

    return $this->response;

  }

  /**
   * Register a new hook
   * 
   * @param string $hook The name of the hook
   * @param closure $callback
   */
  public function hook($hook, $callback) {

    if(isset(static::$hooks[$hook]) and is_array(static::$hooks[$hook])) {
      static::$hooks[$hook][] = $callback;
    } else {
      static::$hooks[$hook] = array($callback);
    }

  }

  /**
   * Trigger a hook
   * 
   * @param string $hook The name of the hook
   * @param mixed $args Additional arguments for the hook
   * @return mixed
   */
  public function trigger($hook, $args = null) {

    // store the triggered hooks to avoid duplications
    static $triggered = array();

    if(isset(static::$hooks[$hook]) and is_array(static::$hooks[$hook])) {
      foreach(static::$hooks[$hook] as $key => $callback) {

        if(in_array($key, $triggered)) continue;

        $triggered[] = $key;

        try {
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

}