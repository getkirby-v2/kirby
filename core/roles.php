<?php

/**
 * Roles
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class RolesAbstract extends Collection {

  // cache for the default role
  protected $default = null;

  /**
   * Constructor
   */
  public function __construct() {

    $roles = kirby::instance()->option('roles');

    // set the default set of roles, if roles are not configured
    if(empty($roles)) {
      $roles = array(
        array(
          'id'          => 'admin',
          'name'        => 'Admin',
          'default'     => true
        ),
        array(
          'id'          => 'editor',
          'name'        => 'Editor',
          'permissions' => array(
            'panel.access'   => true,
            'site.update'    => false,
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
            'user.add'       => false,
            'user.edit'      => false,
            'user.role'      => false,
            'user.delete'    => false
            )
        )
      );
    }

    foreach($roles as $role) {
      $role = new Role($role);
      $this->data[$role->id()] = $role;
    }

    // check for a valid admin role
    if(!isset($this->data['admin'])) {
      throw new Exception('There must be an admin role');
    }

    // check for a valid default role
    if(!$this->findDefault()) {
      $this->data['admin']->default = true;
    }

  }

  /**
   * Returns the default role for new users
   *
   * @return Role
   */
  public function findDefault() {
    if(!is_null($this->default)) return $this->default;
    return $this->default = $this->findBy('isDefault', true);
  }

}
