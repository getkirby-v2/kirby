<?php

namespace Kirby\Component;

use SmartyPantsTypographer_Parser;

/**
 * Kirby Smartypants Parser Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Smartypants extends \Kirby\Component {

  /**
   * Returns the default options for 
   * the smartypants parser
   * 
   * @return array
   */  
  public function defaults() {
    return [
      'smartypants'                   => false,
      'smartypants.attr'              => 1,
      'smartypants.doublequote.open'  => '&#8220;',
      'smartypants.doublequote.close' => '&#8221;',
      'smartypants.space.emdash'      => ' ',
      'smartypants.space.endash'      => ' ',
      'smartypants.space.colon'       => '&#160;',
      'smartypants.space.semicolon'   => '&#160;',
      'smartypants.space.marks'       => '&#160;',
      'smartypants.space.frenchquote' => '&#160;',
      'smartypants.space.thousand'    => '&#160;',
      'smartypants.space.unit'        => '&#160;',
      'smartypants.skip'              => 'pre|code|kbd|script|style|math',    
    ];
  }

  /**
   * Initializes the parser and transforms 
   * the given text. 
   * 
   * @param string $text
   * @return string
   */
  public function parse($text) {
    if(!$this->kirby->options['smartypants']) {
      return $text;
    } else {
      // prepare the text
      $text = str_replace('&quot;', '"', $text);
      // run the parser
      $parser = new SmartyPantsTypographer_Parser($this->kirby->options['smartypants.attr']);
      return $parser->transform($text);      
    }
  }

}