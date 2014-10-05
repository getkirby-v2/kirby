<?php

namespace Kirby;

use Exception;
use R;
use URL;

class Request {

  protected $kirby;

  public function __construct($kirby) {
    $this->kirby = $kirby;
  }

  public function url() {
    return url::current();
  }

  public function params() {
    return new Request\Params(url::params());
  }

  public function query() {
    return new Request\Query(url::query());
  }

  public function path() {
    return new Request\Path($this->kirby->path());
  }

  public function __call($method, $arguments) {
    if(method_exists('r', $method)) {
      return call('r::' . $method, $arguments);
    } else {
      throw new Exception('Invalid method: ' . $method);
    }
  }

}
