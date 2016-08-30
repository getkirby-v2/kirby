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
  protected $permissions = array('*' => true);

  public $default = false;

  public function __construct($data = array()) {

    if(!isset($data['id']))   throw new Exception('The role id is missing');
    if(!isset($data['name'])) throw new Exception('The role name is missing (' . $data['id'] . ')');

    // required data
    $this->id   = $data['id'];
    $this->name = $data['name'];

    if(isset($data['permissions']) and is_array($data['permissions'])) {
      $this->permissions = a::merge($this->permissions, $data['permissions']);
    } else if(isset($data['permissions']) and $data['permissions'] === false) {
      $this->permissions = array('*' => false);
    } else {
      // use the default permissions
    }

    // fallback permissions support for old 'panel' role variable
    if(isset($data['panel']) and is_bool($data['panel'])) {
      $this->permissions['panel.access'] = $data['panel'];
    }

    // sort the permissions by key length, most specific rule comes first
    $keys = array_map('strlen', array_keys($this->permissions));
    array_multisort($keys, SORT_DESC, $this->permissions);

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
    return $this->can('panel.access');
  }

  /**
   * Checks if the role has permission for the specified event
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the permission callbacks
   * @return Obj Object with status() and message() methods
   */
  public function permission($event, $args = []) {

    if(is_string($event)) {
      $action = $event;
      $event = new Kirby\Event($action);
    } else if(is_a($event, 'Kirby\\Event')) {
      $action = $event->type();
    } else {
      throw new Error('Invalid event.');
    }

    // admins always have full access
    if($this->id == 'admin') return new Obj(['status' => true, 'message' => null]);

    foreach($this->permissions as $pattern => $value) {
      // check if the permission matches the event
      if(!fnmatch($pattern, $action)) continue;

      if(is_bool($value)) {
        // simple definition, return it directly
        return new Obj(['status' => $value, 'message' => null]);
      } else if(is_a($value, 'Closure')) {
        // closure, call with args and bound event, expect a boolean or string
        $value = $value->bindTo($event);
        $result = call($value, $args);
        if(is_bool($result)) {
          return new Obj(['status' => $result, 'message' => null]);
        } else if(is_string($result)) {
          // error message
          return new Obj(['status' => false, 'message' => $result]);
        } else {
          throw new Error('Permission ' . $pattern . ' of role ' . $this->id . ' must return a boolean or error string.');
        }
      } else {
        // not boolean or closure, invalid definition
        throw new Error('Permission ' . $pattern . ' of role ' . $this->id . ' is invalid.');
      }
    }

    // no match, no access by default
    return new Obj(['status' => false, 'message' => null]);

  }

  /**
   * Returns true if the role has permission for the specified event
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the permission callbacks
   * @return boolean
   */
  public function can($event, $args = []) {
    return $this->permission($event, $args)->status() === true;
  }

  /**
   * Returns true if the role has *no* permission for the specified event
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the permission callbacks
   * @return boolean
   */
  public function cannot($event, $args = []) {
    return $this->permission($event, $args)->status() === false;
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

  /**
   * Converts the object data to an array
   * 
   * @return array
   */
  public function toArray() {
    return [
      'id'             => $this->id(),
      'name'           => $this->name(),
      'isDefault'      => $this->isDefault(),
      'hasPanelAccess' => $this->hasPanelAccess(),
    ];    
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return array_merge($this->toArray(), [
      'users' => $this->users()
    ]);
  }

}