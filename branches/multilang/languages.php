<?php 

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

  public function __debuginfo() {
    return array_keys($this->data);
  }

}