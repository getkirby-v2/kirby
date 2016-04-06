<?php

namespace Kirby\Component;

/**
 * Kirby Response Builder Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Response extends \Kirby\Component {

  /**
   * Builds and return the response by various input
   * 
   * @param mixed $response
   * @return mixed
   */
  public function make($response) {

    if(is_string($response)) {
      return $this->kirby->render(page($response));
    } else if(is_array($response)) {
      return $this->kirby->render(page($response[0]), $response[1]);
    } else if(is_a($response, 'Page')) {
      return $this->kirby->render($response);      
    } else if(is_a($response, 'Response')) {
      return $response;
    } else {
      return null;
    }

  }

}