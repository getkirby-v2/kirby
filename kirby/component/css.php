<?php

namespace Kirby\Component;

use HTML;

class CSS extends \Kirby\Component {

  public function render($url, $media = null) {

    if(is_array($url)) {
      $css = array();
      foreach($url as $u) $css[] = $this->render($u, $media);
      return implode(PHP_EOL, $css) . PHP_EOL;
    }

    // auto template css files
    if($url == '@auto') {

      $file = $this->kirby->site()->page()->template() . '.css';
      $root = $this->kirby->roots()->autocss() . DS . $file;
      $url  = $this->kirby->urls()->autocss() . '/' . $file;

      if(!file_exists($root)) return false;

    }

    return html::tag('link', null, array(
      'rel'   => 'stylesheet',
      'href'  => url($url),
      'media' => $media
    ));

  }

}