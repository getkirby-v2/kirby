<?php

namespace Kirby\Registry;

use A;
use Exception;
use Kirby;
use Kirby\Registry;

class Method extends Entry {

  protected $subtypes = ['page', 'pages', 'file', 'files', 'field'];

  public function __construct(Kirby $kirby, Registry $registry, $subtype) {
    parent::__construct($kirby, $registry, $subtype);
    if(!in_array($this->subtype, $this->subtypes)) {
      throw new Exception('Invalid method type: ' . $this->subtype . '::method');
    }
  }

  public function set($name, $callback) {
    $class = $this->subtype;
    return $class::$methods[$name] = $callback;
  }

  public function get($name) {
    $class = $this->subtype;
    return a::get($class::$methods, $name);
  }

}