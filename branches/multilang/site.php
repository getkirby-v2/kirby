<?php

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
  public function __construct(Kirby $kirby) {

    parent::__construct($kirby);

    $this->languages = new Languages($this);

    foreach($kirby->options['languages'] as $lang) {

      $language = new Language($this, $lang);

      // store the default language
      if($language->default) $this->defaultLanguage = $this->language = $language;

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
      return parent::url();
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
   * or any other language by language code
   *
   * @param string $code
   * @return Language
   */
  public function language($code = null) {
    if(is_null($code)) return $this->language;
    return $this->languages()->find($code);
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
   * Tries to find the language for the current visitor 
   * 
   * @return Language
   */
  public function visitorLanguage() {
    return $this->languages()->find(visitor::acceptedLanguageCode());
  }

  /**
   * Returns the detected language
   * 
   * @return Language
   */
  public function detectedLanguage() {

    if($language = $this->visitorLanguage()) {
      return $language;
    } else {
      return $this->defaultLanguage();
    }

  }

  /**
   * Returns the language which will be 
   * remembered for the next visit
   * 
   * @return Language
   */
  public function sessionLanguage() {
    if($code = s::get('kirby_language') and $language = $this->languages()->find($code)) {
      return $language;
    } else {
      return null;
    }
  }

  public function switchLanguage(Language $language) {

    s::set('kirby_language', $language->code());

    if($this->language()->code() != $language->code()) {
      go($this->page()->url($language->code()));
    }

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

    // alternate version without file extension
    $baseUri = f::name($uri);
    $parent  = dirname($uri);
    if($parent !== '.') $baseUri = $parent . '/' . $baseUri;

    if(empty($uri)) {
      return $this->page = $this->homePage();
    } else {
      if($lang == $this->defaultLanguage->code and $page = $this->children()->find($uri)) {
        return $this->page = $page;
      } else if($page = $this->children()->findByURI($uri)) {
        return $this->page = $page;
      }

      // store the representation for $page->representation()
      if($uri !== $baseUri) $this->representation = f::extension($uri);

      if($baseUri === '') {
        return $this->page = $this->homePage();
      } else if($page = $this->children()->findByURI($baseUri)) {
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

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return array_merge(parent::__debuginfo(), [
      'languages'        => $this->languages(),
      'language'         => (string)$this->language(),
      'defaultLanguage'  => (string)$this->defaultLanguage(),
      'detectedLanguage' => (string)$this->detectedLanguage(),
      'visitorLanguage'  => (string)$this->visitorLanguage(),
      'sessionLanguage'  => (string)$this->sessionLanguage(),
    ]);
  }

}