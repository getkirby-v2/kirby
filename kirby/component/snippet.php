<?php

namespace Kirby\Component;

use Tpl;

class Snippet extends \Kirby\Component {
  public function render($name, $data = [], $return = false) {
    if(is_object($data)) $data = ['item' => $data];
    return tpl::load($this->kirby->registry->get('snippet', $name), $data, $return);
  }
}