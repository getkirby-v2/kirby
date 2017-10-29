<?php

/**
 * Language
 *
 * A single language object
 */
class Language extends Obj {

  public function __construct($site, $lang) {

    $this->site       = $site;
    $this->code       = $lang['code'];
    $this->name       = $lang['name'];
    $this->locale     = $lang['locale'];
    $this->default    = (isset($lang['default']) and $lang['default']);
    $this->direction  = (isset($lang['direction']) and $lang['direction'] == 'rtl') ? 'rtl' : 'ltr';
    $this->url        = isset($lang['url']) ? rtrim($lang['url'], '/') : $lang['code'];

  }

  public function url() {
    return url::makeAbsolute($this->url, $this->site->url());
  }
  
  public function path() {
    return url::path($this->url);
  }
  
  public function isRoot() {
    return $this->path() === '';
  }
  
  public function host() {
    return (url::isAbsolute($this->url))? url::host($this->url) : false;
  }

  public function isDefault() {
    return $this->default;    
  }

  public function toArray() {
    return [
      'code'      => $this->code(),
      'name'      => $this->name(),
      'url'       => $this->url(),
      'locale'    => $this->locale(),
      'direction' => $this->direction(),
      'isDefault' => $this->isDefault(),
    ];
  }

  public function __toString() {
    return $this->code;
  }

  public function __debuginfo() {
    return $this->toArray();
  }

}