<?php

namespace Kirby\Component;

class TinyUrl extends \Kirby\Component {
  public function route() {
    return [
      'pattern' => $this->kirby->option('tinyurl.folder') . '/(:any)/(:any?)',
      'action'  => function($hash, $lang = null) {          
        // get the site object
        $site = site();
        // make sure the language is set
        $site->visit('/', $lang);
        // find the page by it's tiny hash
        if($page = $site->index()->findBy('hash', $hash)) {
          go($page->url($lang));            
        } else {
          return $site->errorPage();            
        }
      }
    ];
  }
}