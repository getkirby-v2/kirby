<?php

namespace Kirby;

use Exception;
use Kirby;
use Str;

/**
 * Registry
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Registry {

  /**
   * Kirby Instance
   * 
   * @var Kirby
   */
  protected $kirby;

  /**
   * @param Kirby $kirby
   */
  public function __construct(Kirby $kirby) {

    $this->kirby = $kirby;

    // start the registry entry autoloader
    load([
      'kirby\\registry\\entry'      => __DIR__ . DS . 'registry' . DS . 'entry.php',
      'kirby\\registry\\blueprint'  => __DIR__ . DS . 'registry' . DS . 'blueprint.php',      
      'kirby\\registry\\component'  => __DIR__ . DS . 'registry' . DS . 'component.php',
      'kirby\\registry\\controller' => __DIR__ . DS . 'registry' . DS . 'controller.php',
      'kirby\\registry\\hook'       => __DIR__ . DS . 'registry' . DS . 'hook.php',      
      'kirby\\registry\\field'      => __DIR__ . DS . 'registry' . DS . 'field.php',      
      'kirby\\registry\\method'     => __DIR__ . DS . 'registry' . DS . 'method.php',
      'kirby\\registry\\model'      => __DIR__ . DS . 'registry' . DS . 'model.php',
      'kirby\\registry\\option'     => __DIR__ . DS . 'registry' . DS . 'option.php',
      'kirby\\registry\\route'      => __DIR__ . DS . 'registry' . DS . 'route.php',      
      'kirby\\registry\\snippet'    => __DIR__ . DS . 'registry' . DS . 'snippet.php',      
      'kirby\\registry\\template'   => __DIR__ . DS . 'registry' . DS . 'template.php',      
      'kirby\\registry\\tag'        => __DIR__ . DS . 'registry' . DS . 'tag.php',      
      'kirby\\registry\\widget'     => __DIR__ . DS . 'registry' . DS . 'widget.php',      
    ]);

  }

  /**
   * Returns the Kirby instance
   * 
   * @return Kirby
   */
  public function kirby() {
    return $this->kirby;
  }

  /**
   * Returns a registry entry object by type
   * 
   * @param string $type
   * @param string $subtype
   * @return Kirby\Registry\Entry
   */
  public function entry($type, $subtype = null) {

    $class = 'kirby\\registry\\' . $type;

    if(!class_exists('kirby\\registry\\' . $type)) {

      if(str::contains($type, '::')) {
        $parts   = str::split($type, '::');
        $subtype = $parts[0]; 
        $type    = $parts[1]; 
        return $this->entry($type, $subtype);
      }

      throw new Exception('Unsupported registry entry type: ' . $type);
    
    }

    return new $class($this, $subtype);

  } 

  /**
   * Adds a new entry to the registry
   * This will initialize a registry object
   * and call the set method of it 
   * with the passed arguments
   */
  public function set() {
    $args = func_get_args();
    $type = strtolower(array_shift($args));
    return $this->entry($type)->call('set', $args);
  }

  /**
   * Retrieves an entry from the registry
   *
   * This will initialize a registry object
   * and call the get method of it 
   * with the passed arguments
   * 
   * @return Entry
   */
  public function get() {
    $args = func_get_args();
    $type = array_shift($args);
    return $this->entry($type)->call('get', $args);
  }

}