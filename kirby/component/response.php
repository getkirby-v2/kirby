<?php

namespace Kirby\Component;

class Response extends \Kirby\Component {

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