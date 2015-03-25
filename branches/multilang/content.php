<?php 

/**
 * Content
 */
class Content extends ContentAbstract {

  public $language = null;

  /**
   * Constructor
   */
  public function __construct($page, $root) {
    parent::__construct($page, $root);

    // strip the language code from the filename
    // to make sure that the right template is being loaded
    /*
    $expression = '!(\.(' . implode('|', $page->site()->languages->codes()) . '))$!';
    $this->name = preg_replace($expression, '', $this->name);
    */

    $this->name = f::name($this->name);

  }

  public function language() {

    if(!is_null($this->language)) return $this->language;
      
    $codes = $this->page->site()->languages()->codes();
    $code  = f::extension(f::name($this->root));

    return $this->language = in_array($code, $codes) ? $this->page->site()->languages()->find($code) : false;

  }

}