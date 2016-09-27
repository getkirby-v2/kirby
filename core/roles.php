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

    $kirby = kirby();

    // fetch a list of roles from roles dir & registry
    foreach($kirby->get('role') as $roleName) {
      $role = $kirby->get('role', $roleName);
      if(is_array($role)) {
        $role['id'] = $roleName;
        $role = new Role($role);
        $this->data[$role->id()] = $role;
      }
    }

    // fetch roles from the Kirby "roles" option (deprecated)
    foreach($kirby->option('roles') as $role) {
      if(is_array($role)) {
        $role = new Role($role);
        $this->data[$role->id()] = $role;
      }
    }

    // set the default set of roles if roles are not configured
    if(empty($this->data)) {
      $editor = new Role([
        'id'          => 'editor',
        'name'        => 'Editor',
        'permissions' => [
          '*'                 => true,
          'panel.site.update' => false,
          'panel.avatar.*'    => function() {
            return $this->user()->is($this->target()->user());
          },
          'panel.user.*'      => false,
          'panel.user.read'   => true,
          'panel.user.update' => function() {
            return $this->user()->is($this->target()->user());
          }
        ]
      ]);
      $this->data = ['editor' => $editor];
    }

    // check for a valid admin role and provide a default one otherwise
    if(!isset($this->data['admin'])) {
      $this->data['admin'] = new Role([
        'id'   => 'admin',
        'name' => 'Admin'
      ]);
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

  /**
   * Improved @var_dump output
   * 
   * @return array
   */
  public function __debuginfo() {
    return array_keys($this->data);
  }

}