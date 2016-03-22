<?php

namespace Kirby\Registry;

use A;
use Exception;
use Kirby;
use Kirby\Registry;

/**
 * Model Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Model extends Entry {

  /**
   * List of allowed subtypes
   * 
   * @var array $subtypes
   */
  protected $subtypes = ['page'];

  /**
   * @param Kirby\Registry $registry
   * @param string $subtype
   */
  public function __construct(Registry $registry, $subtype) {
    parent::__construct($registry, $subtype);
    if(!in_array($this->subtype, $this->subtypes)) {
      throw new Exception('Invalid model type: ' . $this->subtype . '::model');
    }
  }

  /**
   * Adds a new model to the registry
   * 
   * A model can be registered for any of the allowed
   * subtypes, by using the static method syntax: 
   *
   * $kirby->set('page::model')
   * 
   * The first part of the name is the subtype.
   * The second part of the name is the main type (`model` in this case)
   * 
   * @param string $name
   * @param string $classname Must be a valid classname of a loaded/auto-loaded class
   * @return string
   */  
  public function set($name, $classname) {

    $class = $this->subtype;

    if(!class_exists($classname)) {
      throw new Exception('The model class does not exist: ' . $classname);
    }

    return $class::$models[$name] = $classname;

  }

  /**
   * Retrieves a registered model
   * 
   * @param string $name
   * @return string
   */
  public function get($name) {
    $class = $this->subtype;
    return a::get($class::$models, $name);
  }

}