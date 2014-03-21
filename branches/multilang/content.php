<?php 

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




