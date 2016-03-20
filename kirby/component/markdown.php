<?php

namespace Kirby\Component;

use Parsedown;
use ParsedownExtra;

class Markdown extends \Kirby\Component {

  public function render($markdown) {

    // initialize the right markdown class
    $parsedown = $this->kirby->option('markdown.extra') ? new ParsedownExtra() : new Parsedown();

    // set markdown auto-breaks
    $parsedown->setBreaksEnabled($this->kirby->option('markdown.breaks'));

    // parse it!
    return $parsedown->text($markdown);

  }

}