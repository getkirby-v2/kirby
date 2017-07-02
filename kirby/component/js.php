<?php

namespace Kirby\Component;

use HTML;

/**
 * Kirby Script Tag Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class JS extends \Kirby\Component {

  /**
   * Builds the html script tag for the given javascript file
   * 
   * @param string $src
   * @param boolean|array $async Either true for the async attribute or an array of attributes
   * @return string
   */
  public function tag($src, $async = false) {

    if(is_array($src)) {
      $js = array();
      foreach($src as $s) $js[] = $this->tag($s, $async);
      return implode(PHP_EOL, $js) . PHP_EOL;
    }

    // auto template css files
    if($src == '@auto') {

      $file = $this->kirby->site()->page()->template() . '.js';
      $root = $this->kirby->roots()->autojs() . DS . $file;
      $src  = $this->kirby->urls()->autojs() . '/' . $file;

      if(!file_exists($root)) return false;

    }

    // build the array of HTML attributes
    $attr = array('src' => url($src));
    if(is_array($async)) {
      $attr = array_merge($attr, $async);
    } else if($async === true) {
      $attr['async'] = true;
    }

    return html::tag('script', '', $attr);

  }

}