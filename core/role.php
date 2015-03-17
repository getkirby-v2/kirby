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

  protected $id          = null;
  protected $name        = null;
  protected $panel       = false;
  protected $permissions = array(
      'panel.access'   => true,
      'site.update'    => true,
      'page.create'    => true,
      'page.update'    => true,
      'page.changeurl' => true,
      'page.sort'      => true,
      'page.hide'      => true,
      'page.delete'    => true,
      'file.upload'    => true,
      'file.replace'   => true,
      'file.update'    => true,
      'file.delete'    => true,
      'user.add'       => true,
      'user.edit'      => true,
      'user.role'      => true,
      'user.delete'    => true,
    );

  public $default = false;

  public function __construct($data = array()) {

    if(!isset($data['id']))   throw new Exception('The role id is missing');
    if(!isset($data['name'])) throw new Exception('The role name is missing');


    // required data
    $this->id   = $data['id'];
    $this->name = $data['name'];


    if (isset($data['permissions']) and is_array($data['permissions'])) {
      $this->permissions = a::merge($this->permissions, $data['permissions']);
    } else if (isset($data['permissions']) and $data['permissions'] === false) {
      $this->permissions = array_fill_keys(array_keys($this->permissions), false);
    } else {
      $this->permissions = $this->permissions;
    }

    // fallback permissions support for old 'panel' role variable
    $this->permissions['panel.access'] = (isset($data['panel']) and $data['panel'] === false) ? false : $this->permissions['panel.access'];

    // is this role the default role?
    $this->default = (isset($data['default']) and $data['default'] === true) ? true : false;

  }

  public function id() {
    return $this->id;
  }

  public function name() {
    return $this->name;
  }

  // support for old 'panel' role permission
  public function hasPanelAccess() {
    return $this->hasPermission('panel.access');
  }

  public function hasPermission($target) {
    if($this->id == 'admin')
      return true;
    else
      return (isset($this->permissions[$target]) and $this->permissions[$target] === true) ? true : false;
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
