<?php

/**
 * Role
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class RoleAbstract {

  protected $id    = null;
  protected $name  = null;
  protected $panel = false;

  public $default = false;

  public function __construct($data = array()) {

    if(!isset($data['id']))   throw new Exception('The role id is missing');
    if(!isset($data['name'])) throw new Exception('The role name is missing');

    // make sure to set some important admin defaults
    if($data['id'] == 'admin') {
      $data['panel'] = true;
    }

    // required data
    $this->id   = $data['id'];
    $this->name = $data['name'];

    // does this role have panel access?
    $this->panel   = (isset($data['panel'])   and $data['panel'])   === true ? true : false;
    $this->default = (isset($data['default']) and $data['default']) === true ? true : false;

  }

  public function id() {
    return $this->id;
  }

  public function name() {
    return $this->name;
  }

  public function hasPanelAccess() {
    return $this->panel;
  }

  public function isDefault() {
    return $this->default;
  }

  public function users() {
    return kirby::instance()->site()->users()->filterBy('role', $this->id);
  }

  public function __toString() {
    return $this->id;
  }

}