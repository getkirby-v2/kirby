<?php

/**
 * Page
 *
 * This class represents a single page of
 * a Kirby CMS powered website.
 * A page is derived from a subfolder of the content folder.
 * A page can have unlimited subpages (children)
 * and attached media files. Its custom data is fetched from
 * a text file with separated fields.
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class PageAbstract {

  static public $models  = array();
  static public $methods = array();

  public $kirby;
  public $site;
  public $parent;

  protected $id;
  protected $dirname;
  protected $root;
  protected $depth;
  protected $uid;
  protected $num;
  protected $uri;
  protected $cache = array();

  /**
   * Constructor
   *
   * @param Page $parent
   * @param string $dirname
   */
  public function __construct($parent, $dirname) {

    $this->kirby   = $parent->kirby;
    $this->site    = $parent->site;
    $this->parent  = $parent;
    $this->dirname = $dirname;
    $this->root    = $parent->root() . DS . $dirname;
    $this->depth   = $parent->depth() + 1;

    // extract the uid and num of the directory
    if(preg_match('/^([0-9]+)[\-](.*)$/', $this->dirname, $match)) {
      $this->uid = $match[2];
      $this->num = $match[1];
    } else {
      $this->num = null;
      $this->uid = $this->dirname;
    }

    // assign the uid
    $this->id = $this->uri = ltrim($parent->id() . '/' . $this->uid, '/');

  }

  /**
   * Cleans the temporary page cache and
   * the cache of all parent pages
   */
  public function reset() {
    $this->cache = array();
    $this->parent()->reset();
  }

  /**
   * Mark the page as modified
   */
  public function touch() {
    return touch($this->root());
  }

  /**
   * Returns the kirby object
   *
   * @return Kirby
   */
  public function kirby() {
    return $this->kirby;
  }

  /**
   * Returns the site object
   *
   * @return Site
   */
  public function site() {
    return $this->site;
  }

  /**
   * Returns the parent page element
   *
   * @return Page
   */
  public function parent() {
    return $this->parent;
  }

  /**
   * Returns all parents
   *
   * @return Children
   */
  public function parents() {

    if(isset($this->cache['parents'])) return $this->cache['parents'];

    $children = new Children($this->site);
    $parents  = array();
    $next     = $this->parent();

    while($next and $next->depth() > 0) {
      $children->data[$next->id()] = $next;
      $next = $next->parent();
    }

    return $this->cache['parents'] = $children;

  }

  /**
   * Returns the full root of the page folder
   *
   * @return string
   */
  public function root() {
    return $this->root;
  }

  /**
   * Returns the name of the directory
   *
   * @return string
   */
  public function dirname() {
    return $this->dirname;
  }

  /**
   * Returns the relative URL for the directory.
   * Relative to the base content directory
   *
   * @return string
   */
  public function diruri() {
    if(isset($this->cache['diruri'])) return $this->cache['diruri'];
    return $this->cache['diruri'] = ltrim($this->parent()->diruri() . '/' . $this->dirname(), '/');
  }

  /**
   * Returns the full url for the page
   *
   * @return string
   */
  public function url() {

    if(isset($this->cache['url'])) return $this->cache['url'];

    // Kirby is trying to remove the home folder name from the url
    if($this->isHomePage()) {
      // return the base url
      return $this->cache['url'] = $this->site->url();
    } else if($this->parent->isHomePage()) {
      return $this->cache['url'] = $this->site->url() . '/' . $this->parent->uid . '/' . $this->uid;
    } else {
      $purl = $this->parent->url();
      return $this->cache['url'] = $purl == '/' ? '/' . $this->uid : $this->parent->url() . '/' . $this->uid;
    }

  }

  /**
   * Returns the full URL for the content folder
   * 
   * @return string
   */
  public function contentUrl() {
    return $this->kirby()->urls()->content() . '/' . $this->diruri();
  }

  /**
   * Builds and returns the short url for the current page
   *
   * @return string
   */
  public function tinyurl() {
    if(!isset($this->kirby->options['tinyurl.enabled']) || !$this->kirby->options['tinyurl.enabled']) {
      return $this->url();
    } else {
      // try to use tinyurl.url first, otherwise use tinyurl.folder
      $base = a::get($this->kirby->options, 'tinyurl.url');
      if(!$base) $base = a::get($this->kirby->options, 'tinyurl.folder');
      return url($base . '/' . $this->hash());
    }
  }

  /**
   * Returns a number indicating how deep the page
   * is nested within the content folder
   *
   * @return int
   */
  public function depth() {
    return $this->depth;
  }

  /**
   * Returns the uri for the page
   * which is being used for the url later
   *
   * @return string
   */
  public function uri() {
    return $this->uri;
  }

  /**
   * Returns the id, which is going to be used for
   * Collection keys and things like that
   *
   * @return string
   */
  public function id() {
    return $this->id;
  }

  /**
   * Checks if the page can be cached
   *
   * @return boolean
   */
  public function isCachable() {

    // The error page should not be cached
    if($this->isErrorPage()) {
      return false;
    }

    $lang = ($this->site->defaultLanguage())? $this->site->defaultLanguage()->code : null;
    foreach($this->kirby->option('cache.ignore') as $pattern) {
      if(fnmatch($pattern, $this->uri($lang)) === true) {
        return false;
      }
    }

    return true;

  }

  /**
   * Returns the page uid, which is the
   * folder name without the sorting number
   *
   * @return string
   */
  public function uid() {
    return $this->uid;
  }

  /**
   * Alternative for $this->uid()
   *
   * @return string
   */
  public function slug() {
    return $this->uid;
  }

  /**
   * Returns the sorting number if it exists
   *
   * @return string
   */
  public function num() {
    return $this->num;
  }

  /**
   * Reads the directory and returns an inventory array
   *
   * @return array
   */
  public function inventory() {

    if(isset($this->cache['inventory'])) return $this->cache['inventory'];

    // get all items within the directory
    $ignore = array('.', '..', '.DS_Store', '.git', '.svn', 'Thumb.db');
    $items  = array_diff(scandir($this->root), array_merge($ignore, (array)$this->kirby->option('content.file.ignore')));

    // create the inventory
    $this->cache['inventory'] = array(
      'children' => array(),
      'content'  => array(),
      'meta'     => array(),
      'thumbs'   => array(),
      'files'    => array(),
    );

    // normalize the filename if possible
    if($this->kirby->option('content.file.normalize') && class_exists('Normalizer')) {
      $items = array_map('Normalizer::normalize', $items);
    }

    foreach($items as $item) {

      // skip any invisible files and folders
      if(substr($item, 0, 1) === '.') continue;

      $root = $this->root . DS . $item;

      if(is_dir($root)) {
        $this->cache['inventory']['children'][] = $item;
      } else if(pathinfo($item, PATHINFO_EXTENSION) == $this->kirby->options['content.file.extension']) {
        $this->cache['inventory']['content'][] = $item;
      } else if(strpos($item, '.thumb.') !== false and preg_match('!\.thumb\.(jpg|jpeg|png|gif)$!i', $item)) {
        // get the filename of the original image and use it as the array key
        $image = str_replace('.thumb', '', $item);
        // this makes it easier to find the corresponding image later
        $this->cache['inventory']['thumbs'][$image] = $item;
      } else {
        $this->cache['inventory']['files'][] = $item;
      }

    }

    foreach($this->cache['inventory']['thumbs'] as $key => $thumb) {
      // remove invalid thumbs by looking for a matching image file and
      if(!in_array($key, $this->cache['inventory']['files'])) {
        $this->cache['inventory']['files'][] = $thumb;
        unset($this->cache['inventory']['thumbs'][$key]);
      }
    }

    foreach($this->cache['inventory']['content'] as $key => $content) {
      $file = pathinfo($content, PATHINFO_FILENAME);
      if(in_array($file, $this->cache['inventory']['files'])) {
        $this->cache['inventory']['meta'][$file] = $content;
        unset($this->cache['inventory']['content'][$key]);
      }
    }

    // sort the children
    natsort($this->cache['inventory']['children']);

    return $this->cache['inventory'];

  }

  /**
   * Returns all children for this page
   *
   * @return Children
   */
  public function children() {

    if(isset($this->cache['children'])) return $this->cache['children'];

    $this->cache['children'] = new Children($this);

    $inventory = $this->inventory();

    // with page models
    if(!empty(static::$models)) {
      foreach($inventory['children'] as $dirname) {
        $child = new Page($this, $dirname);
        // let's create a model if one is defined
        if(isset(static::$models[$child->intendedTemplate()])) {
          $model = static::$models[$child->intendedTemplate()];
          $child = new $model($this, $dirname);
        }
        $this->cache['children']->data[$child->id()] = $child;
      }
    // without page models
    } else {
      foreach($inventory['children'] as $dirname) {
        $child = new Page($this, $dirname);
        $this->cache['children']->data[$child->id()] = $child;
      }
    }

    return $this->cache['children'];

  }

  /**
   * Checks if the page has children
   *
   * @return boolean
   */
  public function hasChildren() {
    return $this->children()->count();
  }

  /**
   * Checks if the page has visible children
   *
   * @return boolean
   */
  public function hasVisibleChildren() {
    return $this->children()->visible()->count();
  }

  /**
   * Checks if the page has invisible children
   *
   * @return boolean
   */
  public function hasInvisibleChildren() {
    return $this->children()->invisible()->count();
  }

  /**
   * Returns the grand children of this page
   *
   * @return Children
   */
  public function grandChildren() {
    return $this->children()->children();
  }

  /**
   * Returns the siblings for this page, not including this page
   *
   * @param boolean $self
   * @return Children
   */
  public function siblings($self = true) {
    return $self ? $this->parent->children() : $this->parent->children()->not($this);
  }

  /**
   * Internal method to return the next page
   *
   * @param object $siblings Children A collection of siblings to search in
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return mixed Page or null
   */
  protected function _next(Children $siblings, $sort = array(), $visibility = false) {

    if($sort) $siblings = call(array($siblings, 'sortBy'), $sort);
    $index = $siblings->indexOf($this);
    if($index === false) return null;
    if($visibility) {
      $siblings = $siblings->offset($index+1);
      $siblings = $siblings->{$visibility}();
      return $siblings->first();
    } else {
      return $siblings->nth($index + 1);
    }
  }

  /**
   * Internal method to return the previous page
   *
   * @param object $siblings Children A collection of siblings to search in
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return mixed Page or null
   */
  protected function _prev(Children $siblings, $sort = array(), $visibility = false) {
    if($sort) $siblings = call(array($siblings, 'sortBy'), $sort);
    $index = $siblings->indexOf($this);
    if($index === false or $index === 0) return null;
    if($visibility) {
      $siblings = $siblings->limit($index);
      $siblings = $siblings->{$visibility}();
      return $siblings->last();
    } else {
      return $siblings->nth($index - 1);
    }
  }

  /**
   * Returns the next page element
   *
   * @return Page
   */
  public function next() {
    return $this->_next($this->parent->children(), func_get_args());
  }

  /**
   * Checks if there's a next page
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return boolean
   */
  public function hasNext() {
    return call(array($this, 'next'), func_get_args()) != null;
  }

  /**
   * Returns the next visible page in the current collection if available
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return mixed Page or null
   */
  public function nextVisible() {
    if(!$this->parent) {
      return null;
    } else {
      return $this->_next($this->parent->children(), func_get_args(), 'visible');
    }
  }

  /**
   * Checks if there's a next visible page
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return boolean
   */
  public function hasNextVisible() {
    return call(array($this, 'nextVisible'), func_get_args()) != null;
  }

  /**
   * Returns the next invisible page in the current collection if available
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return mixed Page or null
   */
  public function nextInvisible() {
    if(!$this->parent) {
      return null;
    } else {
      return $this->_next($this->parent->children(), func_get_args(), 'invisible');
    }
  }

  /**
   * Checks if there's a next invisible page
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return boolean
   */
  public function hasNextInvisible() {
    return call(array($this, 'nextInvisible'), func_get_args()) != null;
  }

  /**
   * Returns the previous page element
   *
   * @return Page
   */
  public function prev() {
    return $this->_prev($this->parent->children(), func_get_args());
  }

  /**
   * Checks if there's a previous page
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return boolean
   */
  public function hasPrev() {
    return call(array($this, 'prev'), func_get_args()) != null;
  }

  /**
   * Returns the previous visible page in the current collection if available
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return mixed Page or null
   */
  public function prevVisible() {
    if(!$this->parent) {
      return null;
    } else {
      return $this->_prev($this->parent->children(), func_get_args(), 'visible');
    }
  }

  /**
   * Checks if there's a previous visible page
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return boolean
   */
  public function hasPrevVisible() {
    return call(array($this, 'prevVisible'), func_get_args()) != null;
  }

  /**
   * Returns the previous invisible page in the current collection if available
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return mixed Page or null
   */
  public function prevInvisible() {
    if(!$this->parent) {
      return null;
    } else {
      return $this->_prev($this->parent->children(), func_get_args(), 'invisible');
    }
  }

  /**
   * Checks if there's a previous invisible page
   *
   * @param string $sort An optional sort field for the siblings
   * @param string $direction An optional sort direction
   * @return boolean
   */
  public function hasPrevInvisible() {
    return call(array($this, 'prevInvisible'), func_get_args()) != null;
  }

  /**
   * Find any child or a set of children of this page
   *
   * @return Page | Children
   */
  public function find() {
    return call_user_func_array(array($this->children(), 'find'), func_get_args());
  }

  /**
   * Find any file or a set of files for this page
   *
   * @return File | Files
   */
  public function file() {
    $args = func_get_args();
    if(empty($args)) return $this->files()->first();
    return call_user_func_array(array($this->files(), 'find'), $args);
  }

  // file stuff

  /**
   * Returns all files for this page
   *
   * @return Files
   */
  public function files() {
    if(isset($this->cache['files'])) return $this->cache['files'];
    return $this->cache['files'] = new Files($this);
  }

  /**
   * Checks if this page has attached files
   *
   * @return boolean
   */
  public function hasFiles() {
    return $this->files()->count();
  }

  // File filters
  public function images()    { return $this->files()->filterBy('type', 'image');    }
  public function videos()    { return $this->files()->filterBy('type', 'video');    }
  public function documents() { return $this->files()->filterBy('type', 'document'); }
  public function audio()     { return $this->files()->filterBy('type', 'audio');    }
  public function code()      { return $this->files()->filterBy('type', 'code');     }
  public function archives()  { return $this->files()->filterBy('type', 'archive');  }

  // File checkers
  public function hasImages()    { return $this->images()->count();    }
  public function hasVideos()    { return $this->videos()->count();    }
  public function hasDocuments() { return $this->documents()->count(); }
  public function hasAudio()     { return $this->audio()->count();     }
  public function hasCode()      { return $this->code()->count();      }
  public function hasArchives()  { return $this->archives()->count();  }

  /**
   * Returns a single image
   * 
   * @return File
   */
  public function image($filename = null) {
    if(is_null($filename)) return $this->images()->first();
    return $this->images()->find($filename);
  }

  /**
   * Returns a single video
   * 
   * @return File
   */
  public function video($filename = null) {
    if(is_null($filename)) return $this->videos()->first();
    return $this->videos()->find($filename);
  }

  /**
   * Returns a single document
   * 
   * @return File
   */
  public function document($filename = null) {
    if(is_null($filename)) return $this->documents()->first();
    return $this->documents()->find($filename);
  }


  /**
   * Returns the content object for this page
   *
   * @return Content
   */
  public function content() {

    if(isset($this->cache['content'])) {
      return $this->cache['content'];
    } else {
      $inventory = $this->inventory();
      return $this->cache['content'] = new Content($this, $this->root() . DS . array_shift($inventory['content']));
    }

  }

  /**
   * Returns the title for this page and
   * falls back to the uid if no title exists
   *
   * @return Field
   */
  public function title() {    
    $title = $this->content()->get('title');
    if($title != '') {
      return $title;
    } else {
      $title->value = $this->uid();
      return $title;
    }
  }

  /**
   * Get formatted date fields
   *
   * @param string $format
   * @param string $field
   * @return mixed
   */
  public function date($format = null, $field = 'date') {

    if($timestamp = strtotime($this->content()->$field())) {
      if(is_null($format)) {
        return $timestamp;
      } else {
        return $this->kirby->options['date.handler']($format, $timestamp);
      }
    } else {
      return false;
    }

  }

  /**
   * Returns a unique hashed version of the uri,
   * which is used for the tinyurl for example
   *
   * @return string
   */
  public function hash() {
    if(isset($this->cache['hash'])) return $this->cache['hash'];

    // add a unique hash
    $checksum = sprintf('%u', crc32($this->uri()));
    return $this->cache['hash'] = base_convert($checksum, 10, 36);
  }

  /**
   * Magic getter for all content fields
   *
   * @return Field
   */
  public function __call($key, $arguments = null) {
    if(isset($this->$key)) {
      return $this->$key;
    } else if(isset(static::$methods[$key])) {
      if(!$arguments) $arguments = array();
      array_unshift($arguments, clone $this);
      return call(static::$methods[$key], $arguments);
    } else {
      return $this->content()->get($key, $arguments);
    }
  }

  /**
   * Alternative for $this->equals()
   */
  public function is($page) {
    if(!is_a($page, 'Page')) $page = page($page);

    return $this->id() == $page->id();
  }

  /**
   * Alternative for $this->is()
   */
  public function equals($page) {
    return $this->is($page);
  }

  /**
   * Checks if this page object is the main site
   *
   * @return boolean
   */
  public function isSite() {
    return false;
  }

  /**
   * Checks if this is the active page
   *
   * @return boolean
   */
  public function isActive() {
    return $this->site->page()->is($this);
  }

  /**
   * Checks if the page is open
   *
   * @return boolean
   */
  public function isOpen() {
    if($this->isActive()) return true;
    return $this->site->page()->parents()->has($this);
  }

  /**
   * Checks if the page is visible
   *
   * @return boolean
   */
  public function isVisible() {
    return !is_null($this->num);
  }

  /**
   * Checks if the page is invisible
   *
   * @return boolean
   */
  public function isInvisible() {
    return !$this->isVisible();
  }

  /**
   * Checks if this page is the home page
   * You can define the uri of the homepage in your config
   * file with the home option. By default it's assumed
   * that the homepage folder has the name "home"
   *
   * @return boolean
   */
  public function isHomePage() {
    return $this->uri === $this->kirby->options['home'];
  }

  /**
   * Checks if this page is the error page
   * You can define the uri of the error page in your config
   * file with the error option. By default it's assumed
   * that the error page folder has the name "error"
   *
   * @return boolean
   */
  public function isErrorPage() {
    return $this->uri === $this->kirby->options['error'];
  }

  /**
   * Checks if the page is a child of the given page
   *
   * @param object Page the page object to check
   * @return boolean
   */
  public function isChildOf($page) {
    if(!is_a($page, 'Page')) $page = page($page);

    return $this->is($page) ? false : $this->parent->is($page);
  }

  /**
   * Checks if the page is the parent of the given page
   *
   * @param object Page the page object to check
   * @return boolean
   */
  public function isParentOf($page) {
    if(!is_a($page, 'Page')) $page = page($page);

    return $this->is($page) ? false : $page->parent->is($this);
  }

  /**
   * Checks if the page is a descendant of the given page
   *
   * @param object Page the page object to check
   * @return boolean
   */
  public function isDescendantOf($page) {
    if(!is_a($page, 'Page')) $page = page($page);

    return $this->is($page) ? false : $this->parents()->has($page);
  }

  /**
   * Checks if the page is a descendant of the currently active page
   *
   * @return boolean
   */
  public function isDescendantOfActive() {
    return $this->isDescendantOf($this->site->page());
  }

  /**
   * Checks if the page is an ancestor of the given page
   *
   * @param object Page the page object to check
   * @return boolean
   */
  public function isAncestorOf($page) {
    return $page->isDescendantOf($this);
  }

  /**
   * Checks if the page or any of its files are writable
   *
   * @return boolean
   */
  public function isWritable() {

    $folder = new Folder($this->root());

    if(!$folder->isWritable()) return false;

    foreach($folder->files() as $f) {
      if(!$f->isWritable()) return false;
    }

    return true;

  }

  /**
   * Returns the timestamp when the page
   * has been modified
   *
   * @return int
   */
  public function modified($format = null, $handler = null) {
    return f::modified($this->root, $format, $handler ? $handler : $this->kirby->options['date.handler']);
  }

  /**
   * Returns the index starting from this page
   *
   * @return Children
   */
  public function index() {
    return $this->children()->index();
  }

  /**
   * Search in subpages and all descendants of this page
   *
   * @param string $query
   * @param array $params
   * @return Children
   */
  public function search($query, $params = array()) {
    return $this->children()->index()->search($query, $params);
  }

  // template stuff

  /**
   * Returns the name of the used template
   * The name of the template is defined by the name
   * of the content text file.
   *
   * i.e. text file: project.txt / template name: project
   *
   * This method returns the name of the default template
   * if there's no template with such a name
   *
   * @return string
   */
  public function template() {

    // check for a cached template name
    if(isset($this->cache['template'])) return $this->cache['template'];

    // get the template name
    $templateName = $this->intendedTemplate();

    if($this->kirby->registry->get('template', $templateName)) {
      return $this->cache['template'] = $templateName;  
    } else {
      return $this->cache['template'] = 'default';
    }

  }

  /**
   * Returns the representation file extension for the page
   *
   * @param string $template Template name to use as base
   * @return string/false
   */
  public function representation($template = null) {
    if(!$template) $template = $this->template();
    $cacheKey = 'representation.' . $template;

    // check for a cached representation
    if(isset($this->cache[$cacheKey])) return $this->cache[$cacheKey];

    // check for a representation from the URL
    if($this->site->representation && $this->kirby->registry->get('template', $template . '.' . $this->site->representation)) {
      return $this->cache[$cacheKey] = $this->site->representation;
    }

    // try to get a representation from the Accept header
    // this feature is disabled by default because some browsers
    // have strange Accept headers (e.g. WebKit)
    if($this->kirby->option('representations.accept')) {
      // manually add the normal template to the mix as HTML representation
      $representations = ['__default' => visitor::acceptance('text/html')];

      // add each other available representation
      foreach($this->kirby->registry->get('template', $template, true) as $representation) {
        $representation = f::extension($representation);
        $mime           = f::extensionToMime($representation);

        $representations[$representation] = visitor::acceptance($mime);
      }

      // return the highest accepted representation
      if(!empty($representations) && ($max = max($representations)) > 0) {
        $representation = array_search($max, $representations);
        if($representation === '__default') $representation = false;
        return $this->cache[$cacheKey] = $representation;
      }
    }

    return $this->cache[$cacheKey] = false;
  }

  /**
   * Returns the full path to the used template file
   *
   * @return string
   */
  public function templateFile() {
    return $this->_templateFile($this->intendedTemplate());
  }

  /**
   * Internal helper method
   *
   * @param string $template Template name to use as base
   * @return string
   */
  protected function _templateFile($template) {
    $representation = $this->representation($template);

    if($representation) {
      return $this->kirby->registry->get('template', $template . '.' . $representation);
    } else {
      if($template = $this->kirby->registry->get('template', $template)) {
        return $template;
      } else if($template !== 'default' && $template !== null) {
        // try to get a representation of the default template
        return $this->_templateFile('default');
      } else {
        return $this->kirby->registry->get('template', 'default');
      }
    }
  }

  /**
   * Additional data, which will be passed to the template
   *
   * @return array
   */
  public function templateData() {
    return array();
  }

  /**
   * Returns the name of the content text file / intended template
   * So even if there's no such template it will return the intended name.
   *
   * @return string
   */
  public function intendedTemplate() {
    if(isset($this->cache['intendedTemplate'])) return $this->cache['intendedTemplate'];
    return $this->cache['intendedTemplate'] = $this->content()->exists() ? $this->content()->name() : 'default';
  }

  /**
   * Returns the full path to the intended template file
   * This template file may not exist.
   *
   * @return string
   */
  public function intendedTemplateFile() {
    return $this->kirby->component('template')->file($this->intendedTemplate());
  }

  /**
   * Checks if there's a dedicated template for this page
   * Will return false when the default template is used
   *
   * @return boolean
   */
  public function hasTemplate() {
    return $this->intendedTemplate() == $this->template();
  }

  /**
   * Sends all appropriate headers for this page
   * Can be configured with the headers config array,
   * which should contain all header definitions for each template
   */
  public function headers() {

    $template = $this->template();
    if(isset($this->kirby->options['headers'][$template])) {
      $headers = $this->kirby->options['headers'][$template];

      if(is_numeric($headers)) {
        header::status($headers);
      } else if(is_callable($headers)) {
        call($headers, $this);
      }

    } else if($this->isErrorPage()) {
      header::notfound();
    }

    // send the header of the representation
    if($representation = $this->representation()) {
      if($mime = f::extensionToMime($representation)) header::type($mime);
    }

  }

  /**
   * Returns the root for the content file
   *
   * @return string
   */
  public function textfile($template = null) {
    if(is_null($template)) $template = $this->intendedTemplate();
    return textfile($this->diruri(), $template);
  }

  /**
   * Private method to create a page directory
   */
  static protected function createDirectory($uri) {

    $uid       = str::slug(basename($uri));
    $parentURI = dirname($uri);
    $parent    = ($parentURI == '.' or empty($parentURI) or $parentURI == DS) ? site() : page($parentURI);

    if(!$parent) {
      throw new Exception('The parent does not exist');
    }

    // check for an entered sorting number
    if(preg_match('!^(\d+)\-(.*)!', $uid, $matches)) {
      $num = $matches[1];
      $uid = $matches[2];
      $dir = $num . '-' . $uid;
    } else {
      $num = false;
      $dir = $uid;
    }

    // make sure to check a fresh page
    $parent->reset();

    if($parent->children()->findBy('uid', $uid)) {
      throw new Exception('The page UID exists');
    }

    if(!dir::make($parent->root() . DS . $dir)) {
      throw new Exception('The directory could not be created');
    }

    // make sure the new directory is available everywhere
    $parent->reset();

    return $parent->id() . '/' . $uid;

  }

  /**
   * Creates a new page object
   *
   * @param string $uri
   * @param string $template
   * @param array $data
   */
  static public function create($uri, $template, $data = array()) {

    if(!is_string($template) or empty($template)) {
      throw new Exception('Please pass a valid template name as second argument');
    }

    // try to create the new directory
    $uri = static::createDirectory($uri);

    // create the path for the textfile
    $file = textfile($uri, $template);

    // try to store the data in the text file
    if(!data::write($file, $data, 'kd')) {
      throw new Exception('The page file could not be created');
    }

    // get the new page object
    $page = page($uri);

    if(!is_a($page, 'Page')) {
      throw new Exception('The new page object could not be found');
    }

    // let's create a model if one is defined
    if(isset(static::$models[$template])) {
      $model = static::$models[$template];
      $page = new $model($page->parent(), $page->dirname());
    }

    kirby::instance()->cache()->flush();

    return $page;

  }

  /**
   * Update the page with a new set of data
   *
   * @param array
   */
  public function update($input = array()) {

    $data = a::update($this->content()->toArray(), $input);

    if(!data::write($this->textfile(), $data, 'kd')) {
      throw new Exception('The page could not be updated');
    }

    $this->kirby->cache()->flush();
    $this->reset();
    $this->touch();
    return true;

  }

  /**
   * Increment a field value by one or a given value
   * 
   * @param string $field
   * @param int $by
   * @param int $max
   * @return Page
   */
  public function increment($field, $by = 1, $max = null) {
    $this->update(array(
      $field => function($value) use($by, $max) {
        $new = (int)$value + $by;
        return ($max and $new >= $max) ? $max : $new;
      }
    ));
    return $this;
  }

  /**
   * Decrement a field value by one or a given value
   * 
   * @param string $field
   * @param int $by
   * @param int $min
   * @return Page
   */
  public function decrement($field, $by = 1, $min = 0) {
    $this->update(array(
      $field => function($value) use($by, $min) {
        $new = (int)$value - $by;
        return $new <= $min ? $min : $new;
      }
    ));
    return $this;
  }

  /**
   * Changes the uid for the page
   *
   * @param string $uid
   */
  public function move($uid) {

    $uid = str::slug($uid);

    if(empty($uid)) {
      throw new Exception('The uid is missing');
    }

    // don't do anything if the uid exists
    if($this->uid() === $uid) return true;

    // check for an existing page with the same UID
    if($this->siblings()->not($this)->find($uid)) {
      throw new Exception('A page with this uid already exists');
    }

    $dir  = $this->isVisible() ? $this->num() . '-' . $uid : $uid;
    $root = dirname($this->root()) . DS . $dir;

    if(!dir::move($this->root(), $root)) {
      throw new Exception('The directory could not be moved');
    }

    $this->dirname = $dir;
    $this->root    = $root;
    $this->uid     = $uid;

    // assign a new id and uri
    $this->id = $this->uri = ltrim($this->parent->id() . '/' . $this->uid, '/');

    // clean the cache
    $this->kirby->cache()->flush();
    $this->reset();
    return true;

  }

  /**
   * Return the prepended number for the page
   * or changes it to the number passed as parameter 
   */
  public function sort($num = null) {

    if(!$num and $num !== 0) return $this->num();
    if($num === $this->num()) return true;

    $dir  = $num . '-' . $this->uid();
    $root = dirname($this->root()) . DS . $dir;

    if(!dir::move($this->root(), $root)) {
      throw new Exception('The directory could not be moved');
    }

    $this->dirname = $dir;
    $this->num     = $num;
    $this->root    = $root;
    $this->kirby->cache()->flush();
    $this->reset();
    return true;

  }

  /**
   * Make the page invisible by removing the prepended number
   */
  public function hide() {

    if($this->isInvisible()) return true;

    $root = dirname($this->root()) . DS . $this->uid();

    if(!dir::move($this->root(), $root)) {
      throw new Exception('The directory could not be moved');
    }

    $this->dirname = $this->uid();
    $this->num     = null;
    $this->root    = $root;
    $this->kirby->cache()->flush();
    $this->reset();
    return true;

  }

  public function isDeletable() {

    if($this->isSite())      return false;
    if($this->isHomePage())  return false;
    if($this->isErrorPage()) return false;

    return true;

  }

  /**
   * Deletes the page
   *
   * @param boolean $force Forces the page to be deleted even if there are subpages
   */
  public function delete($force = false) {

    if(!$this->isDeletable()) {
      throw new Exception('The page cannot be deleted');
    }

    if($force === false and $this->children()->count()) {
      throw new Exception('This page has subpages');
    }

    $parent = $this->parent();

    if(!dir::remove($this->root())) {
      throw new Exception('The page could not be deleted');
    }

    $this->kirby->cache()->flush();
    $parent->reset();
    return true;

  }

  /**
   * Converts the entire page object into 
   * a plain PHP array
   * 
   * @param closure $callback Filter callback
   * @return array
   */
  public function toArray($callback = null) {

    $data = [
      'title'            => $this->title()->toString(),
      'id'               => $this->id(),
      'uid'              => $this->uid(),
      'slug'             => $this->slug(),
      'parent'           => $this->parent()->uri(),
      'uri'              => $this->uri(),
      'url'              => $this->url(),
      'contentUrl'       => $this->contentUrl(),
      'tinyUrl'          => $this->tinyUrl(),
      'root'             => $this->root(),
      'dirname'          => $this->dirname(),
      'diruri'           => $this->diruri(),
      'depth'            => $this->depth(),
      'num'              => $this->num(),
      'hash'             => $this->hash(),
      'modified'         => $this->modified('c'),
      'template'         => $this->template(),
      'intendedTemplate' => $this->intendedTemplate(),
      'isVisible'        => $this->isVisible(),
      'isOpen'           => $this->isOpen(),
      'isActive'         => $this->isActive(),
      'isHomePage'       => $this->isHomePage(),
      'isErrorPage'      => $this->isErrorPage(),
      'isCachable'       => $this->isCachable(),
      'isWritable'       => $this->isWritable(),
      'content'          => $this->content()->toArray(),
      'headers'          => $this->headers(),
    ];

    if(is_null($callback)) {
      return $data;
    } else if(is_callable($callback)) {
      return $callback($this);
    }

  }

  /**
   * Tries to find a controller for
   * the current page and loads the data
   *
   * @return array
   */
  public function controller($arguments = array()) {

    // first try to get a controller for the representation
    $controller = null;
    if($representation = $this->representation()) {
      $controller = $this->kirby->registry->get('controller', $this->template() . '.' . $representation);
    }

    // no representation or no special controller: try the normal one
    if(!$controller) $controller = $this->kirby->registry->get('controller', $this->template());

    if(is_a($controller, 'Closure')) {
      return (array)call_user_func_array($controller, array(
        $this->site,
        $this->site->children(),
        $this,
        $arguments
      ));
    }

    return array();

  }

  /**
   * Converts the entire page array into 
   * a json string
   * 
   * @param closure $callback Filter callback
   * @return string
   */
  public function toJson($callback = null) {
    return json_encode($this->toArray($callback));
  }

  /**
   * Makes it possible to echo the entire object
   *
   * @return string
   */
  public function __toString() {
    return (string)$this->id();
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {

    $data = $this->toArray();

    return array_merge($data, [
      'content'    => $this->content(),
      'children'   => $this->children(),
      'siblings'   => $this->siblings(false),
      'files'      => $this->files(),
    ]);
  }

}