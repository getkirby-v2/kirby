<?php

/**
 * Helper to create extended page objects
 *
 * @param mixed $page
 */
class PageExtension extends Page {
  public function __construct($page) {
    $page = is_string($page) ? page($page) : $page;
    parent::__construct($page->parent(), $page->dirname());
  }
}