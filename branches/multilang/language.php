<?php 

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


