<?php 

/**
 * Content
 */
class Content extends ContentAbstract {

  public $language = null;

  /**
   * Constructor
   */
  public function __construct($page, $root, $language) {

    parent::__construct($page, $root);

    $this->name     = f::name($this->name);
    $this->language = $language;

  }

  public function realroot() {
    return dirname($this->root()) . DS . $this->name() . '.' . $this->language . '.' . f::extension($this->root());
  }

  public function exists() {
    return file_exists($this->realroot());
  }

  public function language() {

    if(!is_null($this->language)) return $this->language;
      
    $codes = $this->page->site()->languages()->codes();
    $code  = f::extension(f::name($this->root));

    return $this->language = in_array($code, $codes) ? $this->page->site()->languages()->find($code) : false;

  }

}