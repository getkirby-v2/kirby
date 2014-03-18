<?php 

/**
 * Field
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class FieldAbstract extends stdClass {
  public function __toString() {
    return $this->value;
  }
}