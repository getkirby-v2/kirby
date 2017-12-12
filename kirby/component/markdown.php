<?php

namespace Kirby\Component;

use Parsedown;
use ParsedownExtra;
use Field;

/**
 * Kirby Markdown Parser Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Markdown extends \Kirby\Component {

  /**
   * Returns the default options for the component
   *
   * @return array
   */
  public function defaults() {
    return [
      'markdown'        => true,
      'markdown.extra'  => false,
      'markdown.breaks' => true,
    ];
  }

  /**
   * Initializes the Parsedown parser and
   * transforms the given markdown to HTML
   *
   * @param string $markdown
   * @param Field $text
   * @return string
   */
  public function parse($markdown, Field $field = null) {

    if(!$this->kirby->options['markdown']) {
      return $markdown;
    } else {
      // initialize the right markdown class
      $parsedown = $this->kirby->options['markdown.extra'] ? new ParsedownExtra() : new Parsedown();

      // set markdown auto-breaks
      $parsedown->setBreaksEnabled($this->kirby->options['markdown.breaks']);

      // parse it!
      return $parsedown->text($markdown);
    }

  }

}
