<?php

/**
 * Helper to create extended page objects
 *
 * @param mixed $page
 */
class PageExtension extends Page {
  public function __construct($page) {
    $page = is_string($page) ? page($page) : $page;    
    if($page) {
      parent::__construct($page->parent(), $page->dirname());      
    } else {
      throw new Exception('The page could not be found');
    }
  }
}