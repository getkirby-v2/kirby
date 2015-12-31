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
      'panel.access'         => true,
      'panel.site.update'    => true,
      'panel.page.create'    => true,
      'panel.page.update'    => true,
      'panel.page.move'      => true,
      'panel.page.sort'      => true,
      'panel.page.hide'      => true,
      'panel.page.delete'    => true,
      'panel.file.upload'    => true,
      'panel.file.replace'   => true,
      'panel.file.update'    => true,
      'panel.file.delete'    => true,
      'panel.user.add'       => true,
      'panel.user.edit'      => true,
      'panel.user.role'      => true,
      'panel.user.delete'    => true,
    );

  public $default = false;

  public function __construct($data = array()) {

    if(!isset($data['id']))   throw new Exception('The role id is missing');
    if(!isset($data['name'])) throw new Exception('The role name is missing');

    // required data
    $this->id   = $data['id'];
    $this->name = $data['name'];

    if(isset($data['permissions']) and is_array($data['permissions'])) {
      $this->permissions = a::merge($this->permissions, $data['permissions']);
    } else if(isset($data['permissions']) and $data['permissions'] === false) {
      $this->permissions = array_fill_keys(array_keys($this->permissions), false);
    } else {
      $this->permissions = $this->permissions;
    }

    // fallback permissions support for old 'panel' role variable
    if(isset($data['panel']) and is_bool($data['panel'])) {
      $this->permissions['panel.access'] = $data['panel'];
    }

    // is this role the default role?
    if(isset($data['default'])) {
      $this->default = $data['default'] === true;
    }

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
    if($this->id == 'admin') {
      return true;
    } else if(isset($this->permissions[$target]) and $this->permissions[$target] === true) {
      return true;
    } else {
      return false;
    }
  }

  public function isDefault() {
    return $this->default;
  }

  public function users() {
    return kirby::instance()->site()->users()->filterBy('role', $this->id);
  }

  public function __toString() {
    return (string)$this->id;
  }

}
