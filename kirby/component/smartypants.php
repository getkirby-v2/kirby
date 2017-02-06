<?php

namespace Kirby\Component;

use \Michelf\SmartyPantsTypographer;

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
      'smartypants'                            => false,
      'smartypants.attr'                       => 1,
      'smartypants.doublequote.open'           => '&#8220;',
      'smartypants.doublequote.close'          => '&#8221;',
      'smartypants.doublequote.low'            => '&#8222;',
      'smartypants.singlequote.open'           => '&#8216;',
      'smartypants.singlequote.close'          => '&#8217;',
      'smartypants.backtick.doublequote.open'  => '&#8220;',
      'smartypants.backtick.doublequote.close' => '&#8221;',
      'smartypants.backtick.singlequote.open'  => '&#8216;',
      'smartypants.backtick.singlequote.close' => '&#8217;',
      'smartypants.emdash'                     => '&#8212;',
      'smartypants.endash'                     => '&#8211;',
      'smartypants.ellipsis'                   => '&#8230;',
      'smartypants.space'                      => '(?: | |&nbsp;|&#0*160;|&#x0*[aA]0;)',
      'smartypants.space.emdash'               => ' ',
      'smartypants.space.endash'               => ' ',
      'smartypants.space.colon'                => '&#160;',
      'smartypants.space.semicolon'            => '&#160;',
      'smartypants.space.marks'                => '&#160;',
      'smartypants.space.frenchquote'          => '&#160;',
      'smartypants.space.thousand'             => '&#160;',
      'smartypants.space.unit'                 => '&#160;',
      'smartypants.guillemet.leftpointing'     => '&#171;',
      'smartypants.guillemet.rightpointing'    => '&#187;', 
      'smartypants.geresh'                     => '&#1523;',
      'smartypants.gershayim'                  => '&#1524;',
      'smartypants.skip'                       => 'pre|code|kbd|script|style|math',          
    ];
  }

  /**
   * Initializes the parser and transforms 
   * the given text. 
   * 
   * @param string $text
   * @param boolean $force
   * @return string
   */
  public function parse($text, $force = false) {
    if($this->kirby->options['smartypants'] === true || $force === true) {

      // prepare the text
      $text = str_replace('&quot;', '"', $text);

      // start the parser
      $parser = new SmartyPantsTypographer($this->kirby->options['smartypants.attr']);
      
      // configuration
      $parser->smart_doublequote_open     = $this->kirby->options['smartypants.doublequote.open'];
      $parser->smart_doublequote_close    = $this->kirby->options['smartypants.doublequote.close'];
      $parser->smart_singlequote_open     = $this->kirby->options['smartypants.singlequote.open'];
      $parser->smart_singlequote_close    = $this->kirby->options['smartypants.singlequote.close'];      
      $parser->backtick_doublequote_open  = $this->kirby->options['smartypants.backtick.doublequote.open'];
      $parser->backtick_doublequote_close = $this->kirby->options['smartypants.backtick.doublequote.close'];
      $parser->backtick_singlequote_open  = $this->kirby->options['smartypants.backtick.singlequote.open'];
      $parser->backtick_singlequote_close = $this->kirby->options['smartypants.backtick.singlequote.close'];
      $parser->em_dash                    = $this->kirby->options['smartypants.emdash'];
      $parser->en_dash                    = $this->kirby->options['smartypants.endash'];
      $parser->ellipsis                   = $this->kirby->options['smartypants.ellipsis'];
      $parser->tags_to_skip               = $this->kirby->options['smartypants.skip'];
      $parser->space_emdash               = $this->kirby->options['smartypants.space.emdash'];
      $parser->space_endash               = $this->kirby->options['smartypants.space.endash'];
      $parser->space_colon                = $this->kirby->options['smartypants.space.colon'];
      $parser->space_semicolon            = $this->kirby->options['smartypants.space.semicolon'];
      $parser->space_marks                = $this->kirby->options['smartypants.space.marks'];
      $parser->space_frenchquote          = $this->kirby->options['smartypants.space.frenchquote'];
      $parser->space_thousand             = $this->kirby->options['smartypants.space.thousand'];
      $parser->space_unit                 = $this->kirby->options['smartypants.space.unit'];      
      $parser->doublequote_low            = $this->kirby->options['smartypants.doublequote.low'];
      $parser->guillemet_leftpointing     = $this->kirby->options['smartypants.guillemet.leftpointing'];
      $parser->guillemet_rightpointing    = $this->kirby->options['smartypants.guillemet.rightpointing'];
      $parser->geresh                     = $this->kirby->options['smartypants.geresh'];
      $parser->gershayim                  = $this->kirby->options['smartypants.gershayim'];
      $parser->space                      = $this->kirby->options['smartypants.space'];
      
      return $parser->transform($text);      
    } else {
      return $text;
    }
  }

}