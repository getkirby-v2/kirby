<?php

namespace Kirby\Component;

/**
 * Kirby TinyUrl Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class TinyUrl extends \Kirby\Component {

  /**
   * Returns the default options for the tinyurl component
   * 
   * @return array
   */
  public function defaults() {
    return [
      'tinyurl.enabled' => true,
      'tinyurl.folder'  => 'x',    
    ];
  }

  /**
   * Returns the tinyurl fetching route
   * 
   * @return array
   */
  public function route() {
    if(!$this->kirby->options['tinyurl.enabled']) {
      return false;
    } else {
      return [
        'pattern' => $this->kirby->options['tinyurl.folder'] . '/(:any)/(:any?)',
        'action'  => function($hash, $lang = null) {          
          // get the site object
          $site = site();
          // make sure the language is set
          $site->visit('/', $lang);
          // find the page by it's tiny hash
          if($page = $site->index()->findBy('hash', $hash)) {
            go($page->url($lang));            
          } else {
            return $site->errorPage();            
          }
        }
      ];      
    }
  }
}