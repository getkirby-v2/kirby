<?php

namespace Kirby;

use Obj;

/**
 * Event
 *
 * @package   Kirby CMS
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Event extends Obj {

  /**
   * Constructs a new event
   *
   * @param string $type Name of the event
   * @param array  $data Additional data
   */
  public function __construct($type, $data = []) {
    $this->type = $type;
    parent::__construct($data);
  }

  /**
   * Helper methods
   */
  public function kirby() {
    return kirby();
  }

  public function site() {
    return site();
  }

  public function user() {
    return site()->user();
  }

  public function username() {
    $user = $this->user();
    return ($user)? $user->username() : null;
  }

}
