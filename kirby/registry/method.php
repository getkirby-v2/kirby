<?php

namespace Kirby\Registry;

use A;
use Exception;
use Kirby;
use Kirby\Registry;

/**
 * Method Registy Entry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Method extends Entry {

  /**
   * List of allowed subtypes
   * 
   * @var array $subtypes
   */
  protected $subtypes = ['site', 'page', 'pages', 'file', 'files', 'field'];

  /**
   * @param Kirby\Registry $registry
   * @param string $subtype
   */
  public function __construct(Registry $registry, $subtype) {
    parent::__construct($registry, $subtype);
    if(!in_array($this->subtype, $this->subtypes)) {
      throw new Exception('Invalid method type: ' . $this->subtype . '::method');
    }
  }

  /**
   * Adds a new method to the registry
   * 
   * A method can be registered for any of the allowed
   * subtypes, by using the static method syntax: 
   * $kirby->set('page::method')
   * $kirby->set('field::method') 
   * etc.
   * 
   * The first part of the name is the subtype.
   * The second part of the name is the main type (`method` in this case)
   * 
   * @param string $name
   * @param Closure $callback
   * @return Closure
   */
  public function set($name, $callback) {
    $class = $this->subtype;
    return $class::$methods[$name] = $callback;
  }

  /**
   * Retrieves a registered method 
   * 
   * @param string $name
   * @return Closure
   */
  public function get($name) {
    $class = $this->subtype;
    return a::get($class::$methods, $name);
  }

}