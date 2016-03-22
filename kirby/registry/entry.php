<?php

namespace Kirby\Registry;

use Kirby;
use Kirby\Registry;

/**
 * Registy Entry
 * 
 * Base Entry Class. All other registry entries
 * must extend this class to inherit basic 
 * functionalities of registry entries and to 
 * be accepted by the registry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class Entry {

  /**
   * Kirby instance 
   * 
   * @var Kirby 
   */
  protected $kirby;

  /**
   * Kirby Registry instance
   * 
   * @var Kirby\Registry
   */
  protected $registry;

  /**
   * Optional subtype for something
   * like $kirby->set('field::method', '…')
   * where `field` is the subtype of type `method`.
   * 
   * @param string $subtype
   */
  protected $subtype;

  /**
   * @param Kirby $kirby
   * @param Kirby\Registry $registry
   * @param string $subtype
   */
  public function __construct(Kirby $kirby, Registry $registry, $subtype = null) {
    $this->kirby    = $kirby;
    $this->registry = $registry;
    $this->subtype  = $subtype;
  }

  /**
   * Interface to call any registry entry method
   * 
   * Mostly used for set() and get()
   * 
   * @param string $method
   * @param array $args
   * @return mixed
   */
  public function call($method, $args) {
    return call([$this, $method], $args);
  }

}