<?php

namespace Kirby\Component;

use SmartyPantsTypographer_Parser;

class Smartypants extends \Kirby\Component {

  public function render($text) {
    $parser = new SmartyPantsTypographer_Parser($this->kirby->option('smartypants.attr', 1));
    return $parser->transform($text);
  }

}