<?php

namespace Kirby\Registry;

use A;
use Exception;
use Kirby;
use Kirby\Registry;

class Model extends Entry {

  protected $subtypes = ['page'];

  public function __construct(Kirby $kirby, Registry $registry, $subtype) {
    parent::__construct($kirby, $registry, $subtype);
    if(!in_array($this->subtype, $this->subtypes)) {
      throw new Exception('Invalid model type: ' . $this->subtype . '::model');
    }
  }

  public function set($name, $classname) {

    $class = $this->subtype;

    if(!class_exists($classname)) {
      throw new Exception('The model class does not exist: ' . $classname);
    }

    return $class::$models[$name] = $classname;

  }

  public function get($name) {
    $class = $this->subtype;
    return a::get($class::$models, $name);
  }

}