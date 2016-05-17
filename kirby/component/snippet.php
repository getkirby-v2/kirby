<?php

namespace Kirby\Component;

use Tpl;

/**
 * Kirby Snippet Builder Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Snippet extends \Kirby\Component {

  /**
   * Returns a snippet file path by name
   *
   * @param string $name
   * @return string
   */
  public function file($name) {
    return $this->kirby->roots()->snippets() . DS . str_replace('/', DS, $name) . '.php';
  }

  /**
   * Renders the snippet with the given data 
   * 
   * @param string $name
   * @param array $data
   * @param boolean $return
   * @return string
   */
  public function render($name, $data = [], $return = false) {
    if(is_object($data)) $data = ['item' => $data];
    return tpl::load($this->kirby->registry->get('snippet', $name), $data, $return);
  }

}