<?php

namespace Kirby\Component;

use HTML;

class JS extends \Kirby\Component {

  public function render($src, $async = false) {

    if(is_array($src)) {
      $js = array();
      foreach($src as $s) $js[] = $this->render($s, $async);
      return implode(PHP_EOL, $js) . PHP_EOL;
    }

    // auto template css files
    if($src == '@auto') {

      $file = $this->kirby->site()->page()->template() . '.js';
      $root = $this->kirby->roots()->autojs() . DS . $file;
      $src  = $this->kirby->urls()->autojs() . '/' . $file;

      if(!file_exists($root)) return false;

    }

    return html::tag('script', '', array(
      'src'   => url($src),
      'async' => $async
    ));

  }

}