<?php

/**
 * Page
 */
class Page extends PageAbstract {

  /**
   * Returns the root for the content file
   *
   * @return string
   */
  public function textfile($template = null, $lang = null) {
    if(is_null($template)) $template = $this->intendedTemplate();
    if(is_null($lang))     $lang     = $this->site->language->code;
    return textfile($this->diruri(), $template, $lang);
  }

  /**
   * Returns the translated URI
   */
  public function uri($lang = null) {
    // build the page's uri with the parent uri and the page's slug
    return ltrim($this->parent->uri($lang) . '/' . $this->slug($lang), '/');
  }

  /**
   * Returns the cache id
   *
   * @return string
   */
  public function cacheId($lang = null) {
    if(is_null($lang)) $lang = $this->site->language->code;
    return $lang . '.' . parent::cacheId();
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
    $expression  = '!(.*?)(\.(' . implode('|', $this->site->languages->codes()) . ')|)\.' . $this->kirby->options['content.file.extension'] . '$!i';

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

  /**
   * Creates a new page object
   *
   * @param string $uri
   * @param string $template
   * @param array $data
   */
  static public function create($uri, $template, $data = array()) {
    return parent::create($uri, $template . '.' . site()->defaultLanguage->code, $data);
  }

  /**
   * Update the page with a new set of data
   *
   * @param array $data
   */
  public function update($data = array(), $lang = null) {

    $data = array_merge($this->content()->toArray(), $data);

    if(!data::write($this->textfile(null, $lang), $data, 'kd')) {
      throw new Exception('The page could not be updated');
    }

    cache::flush();
    $this->reset();
    $this->touch();
    return true;

  }

}