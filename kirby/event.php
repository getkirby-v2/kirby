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
class Event {

  public $type;
  public $kirby;
  public $site;
  public $target;
  public $user;

  /**
   * Constructs a new event
   *
   * @param string $type Name of the event
   * @param array  $data Additional data
   */
  public function __construct($type, $target = []) {
    $this->type   = $type;
    $this->target = new Obj($target);
  }

  public function type() {
    return $this->type;
  }

  public function target() {
    return $this->target;
  }

  public function kirby() {
    return kirby();
  }

  public function site() {
    if(is_a($this->site, 'Site')) {
      return $this->site;
    } else {
      return $this->site = site();
    }
  }

  public function user() {
    if(is_a($this->user, 'User')) {
      return $this->user;
    } else {
      return $this->user = $this->site()->user();      
    }
  }

  public function username() {
    $user = $this->user();
    return $user ? $user->username() : null;
  }

  public function role() {
    $user = $this->user();
    return $user ? $user->role() : null;
  }

  public function language() {
    $user = $this->user();
    return $user ? $user->language() : null;
  }

}