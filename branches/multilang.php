<?php 

/**
 * Unmodified classes
 */
class Children  extends ChildrenAbstract {}
class Field     extends FieldAbstract {}
class Files     extends FilesAbstract {}
class Kt        extends KtAbstract {}
class Ktag      extends KtagAbstract {}
class Blueprint extends BlueprintAbstract {}
class Users extends UsersAbstract {}
class User extends UserAbstract {}

/**
 * File
 */
class File extends FileAbstract {

  /**
   * Get the meta information 
   * 
   * @param string $lang optional language code
   * @return Content
   */
  public function meta($lang = null) {

    // get the content for the current language
    if(is_null($lang)) {

      // the current language's content can be cached
      if(isset($this->cache['meta'])) return $this->cache['meta'];

      // get the current content
      $meta = $this->_meta($this->site->language->code);

      // get the fallback content 
      if($this->site->language->code != $this->site->defaultLanguage->code) {

        // fetch the default language content
        $defaultMeta = $this->_meta($this->site->defaultLanguage->code);

        // replace all missing fields with values from the default content
        foreach($defaultMeta->data as $key => $field) {      
          if(empty($meta->data[$key]->value)) {
            $meta->data[$key] = $field;            
          }
        }
        
      }

      // cache the meta for this language
      return $this->cache['meta'] = $meta;

    // get the meta for another language
    } else {    
      return $this->_meta($lang);
    }

  }

  /**
   * Private method to simplify meta fetching
   * 
   * @return Content
   */
  protected function _meta($lang) {

    // get the inventory
    $inventory = $this->page->inventory();      

    // try to fetch the content for this language
    $meta = isset($inventory['meta'][$this->filename][$lang]) ? $inventory['meta'][$this->filename][$lang] : null;

    // try to replace empty content with the default language content
    if(empty($meta) and isset($inventory['meta'][$this->filename][$this->site->defaultLanguage->code])) {
      $meta = $inventory['meta'][$this->filename][$this->site->defaultLanguage->code];
    }

    // find and cache the content for this language
    return new Content($this->page, $this->page->root() . DS . $meta);

  }

}


/**
 * Content
 */
class Content extends ContentAbstract {

  /**
   * Constructor
   */
  public function __construct($page, $root) {
    parent::__construct($page, $root);

    // strip the language code from the filename
    // to make sure that the right template is being loaded
    $expression = '!(\.(' . implode('|', $page->site()->languages->codes()) . '))$!';
    $this->name = preg_replace($expression, '', $this->name);

  }

}


/**
 * Page
 */
class Page extends PageAbstract {

  /** 
   * Returns the translated URI
   */
  public function uri($lang = null) {
    // build the page's uri with the parent uri and the page's slug
    return ltrim($this->parent->uri($lang) . '/' . $this->slug($lang), '/');
  }

  /** 
   * Returns the slug for the page
   * The slug is the last part of the URL path
   * For multilang sites this can be translated with a URL-Key field 
   * in the text file for this page. 
   * 
   * @param string $lang Optional language code to get the translated slug
   * @return string i.e. 01-projects returns projects
   */
  public function slug($lang = null) {

    $default = $this->site->defaultLanguage->code; 
    $current = $this->site->language->code;

    // get the slug for the current language
    if(is_null($lang)) {

      // the current language's slug can be cached
      if(isset($this->cache['slug'])) return $this->cache['slug'];

      // if the current language is the default language
      // simply return the uid
      if($current == $default) {
        return $this->cache['slug'] = $this->uid();
      }

      // geth the translated url key if available
      $key = (string)a::get((array)$this->content()->data(), 'url_key');

      // return the translated slug or otherwise the uid
      return (empty($key)) ? $this->uid() : $key;              

    } else {

      // if the passed language code is the current language code
      // we can simply return the slug method without a language code specified
      if($lang == $current) {
        return $this->slug();
      } 

      // the slug for the default language is just the name of the folder
      if($lang == $default) {
        return $this->uid();        
      }

      // search for content in the specified language
      if($content = $this->content($lang)) {            
        // search for a translated url_key in that language
        if($slug = a::get((array)$content->data(), 'url_key')) {
          // if available, use the translated url key as slug
          return str::slug($slug);
        }
      } 

      // use the uid if no translation could be found
      return $this->uid();

    }

  }

  /** 
   * Returns the full url for the page
   * 
   * @param string $lang Optional language code to get the URL for that specific language on multilang sites
   * @return string
   */
  public function url() {

    $args = func_get_args();
    $lang = array_shift($args);

    // for multi language sites every url needs
    // to be treated specially to make sure each uid is translated properly
    // and language codes are prepended if needed
    if(is_null($lang)) {
      // get the current language
      $lang = $this->site->language->code;
    } 

    // Kirby is trying to remove the home folder name from the url
    if($this->isHomePage()) {
      return $this->site->url($lang);                    
    } else if($this->parent->isHomePage()) {
      return $this->site->url($lang) . '/' . $this->parent->uid() . '/' . $this->slug($lang); 
    } else {
      return $this->parent->url($lang) . '/' . $this->slug($lang);        
    }

  }

  /**
   * Modified inventory fetcher
   * 
   * @return array
   */
  public function inventory() {

    $inventory   = parent::inventory();
    $defaultLang = $this->site->defaultLanguage->code;
    $expression  = '!(.*?)(\.(' . implode('|', $this->site->languages->codes()) . ')|)\.' . $this->site->options['content.file.extension'] . '$!i';

    foreach($inventory['meta'] as $key => $meta) {
      $inventory['meta'][$key] = array($defaultLang => $meta);
    }

    foreach($inventory['content'] as $key => $content) {

      preg_match($expression, $content, $match);

      $file = $match[1];
      $lang = isset($match[3]) ? $match[3] : $defaultLang;

      if(in_array($file, $inventory['files'])) {
        $inventory['meta'][$file][$lang] = $content;
      } else {
        $inventory['content'][$lang] = $content;
      }

      unset($inventory['content'][$key]);

    }

    return $inventory;

  }

  /**
   * Returns the content object for this page
   * 
   * @param string $lang optional language code
   * @return Content
   */
  public function content($lang = null) {

    // get the content for the current language
    if(is_null($lang)) {

      // the current language's content can be cached
      if(isset($this->cache['content'])) return $this->cache['content'];

      // get the current content
      $content = $this->_content($this->site->language->code);

      // get the fallback content 
      if($this->site->language->code != $this->site->defaultLanguage->code) {

        // fetch the default language content
        $defaultContent = $this->_content($this->site->defaultLanguage->code);

        // replace all missing fields with values from the default content
        foreach($defaultContent->data as $key => $field) {      
          if(empty($content->data[$key]->value)) {
            $content->data[$key] = $field;            
          }
        }

      }

      // find and cache the content for this language
      return $this->cache['content'] = $content;

    // get the content for another language
    } else {    
      return $this->_content($lang);
    }

  }

  /**
   * Private method to simplify content fetching
   * 
   * @return Content
   */
  protected function _content($lang) {

    // get the inventory
    $inventory = $this->inventory();      

    // try to fetch the content for this language
    $content = isset($inventory['content'][$lang]) ? $inventory['content'][$lang] : null;

    // try to replace empty content with the default language content
    if(empty($content) and isset($inventory['content'][$this->site->defaultLanguage->code])) {
      $content = $inventory['content'][$this->site->defaultLanguage->code];
    }

    // find and cache the content for this language
    return new Content($this, $this->root() . DS . $content);

  }

}


/**
 * Languages
 * 
 * Holds all available Language objects for the site
 */
class Languages extends Collection {

  protected $site = null;

  public function __construct($site) {
    return $this->site = $site;
  }

  public function find($code) {
    return isset($this->data[$code]) ? $this->data[$code] : null;
  }

  public function codes() {
    return $this->keys();  
  }

  public function findDefault() {
    return $this->site->defaultLanguage();
  }

}


/**
 * Language
 * 
 * A single language object
 */
class Language extends Obj {

  public function __construct($site, $lang) {

    $this->code    = $lang['code'];
    $this->name    = $lang['name'];
    $this->locale  = $lang['locale'];
    $this->default = isset($lang['default']);
    $this->url     = str::template($lang['url'], array('site.base' => $site->url()));

  }

}


/**
 * Site
 * 
 * Modified Site object
 */
class Site extends SiteAbstract {

  public $languages;
  public $language;
  public $defaultLanguage;

  /**
   * Constructor
   */
  public function __construct($options = array()) {

    parent::__construct($options);

    $this->languages = new Languages($this);

    foreach($options['languages'] as $lang) {

      $language = new Language($this, $lang);

      // store the default language
      if($language->default) $this->defaultLanguage = $language;

      // add the language to the collection
      $this->languages->data[$language->code] = $language;

    }

  }

  /** 
   * Returns the translated URI
   */
  public function uri($lang = null) {
    return null;
  }

  public function slug($lang = null) {
    return null;
  }

  /**
   * Returns the url of the site
   * 
   * @return string
   */
  public function url($lang = false) {
    if($lang) {
      // return the specific language url
      return $this->languages->find($lang)->url();
    } else {
      return $this->options['url'];
    }
  }

  /**
   * Marks the site as a multilanguage site
   * 
   * @return boolean
   */
  public function multilang() {
    return true;
  }

  /**
   * Returns the Languages Collection
   * 
   * @return Languages
   */
  public function languages() {
    return $this->languages;
  }

  /**
   * Returns the current language
   * 
   * @return Language
   */
  public function language() {
    return $this->language;
  }

  /**
   * Returns the default language 
   * 
   * @return Language
   */
  public function defaultLanguage() {
    return $this->defaultLanguage;
  }

  /**
   * Sets the currently active page
   * and returns its page object
   * 
   * @param string $uri
   * @return Page
   */
  public function visit($uri = '', $lang = null) {    
    
    // if the language code is missing or the code is invalid (TODO)
    if(!in_array($lang, $this->languages()->keys())) {
      $lang = $this->defaultLanguage->code;
    }
    
    // set the current language
    $this->language = $this->languages()->data[$lang];

    // clean the uri
    $uri = trim($uri, '/');

    if(empty($uri)) {
      return $this->page = $this->homePage();
    } else {
      
      if($lang == $this->defaultLanguage->code and $page = $this->children()->find($uri)) {
        return $this->page = $page;
      } else if($page = $this->children()->findByURI($uri)) {
        return $this->page = $page;
      } else {
        return $this->page = $this->errorPage();
      }
    }

  }

  /**
   * Returns the locale for the site
   * 
   * @return string
   */
  public function locale() {
    return $this->language->locale;
  }

}

