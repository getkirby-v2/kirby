<?php

/**
 * User
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class UserAbstract {

  protected $username = null;
  protected $cache = array();
  protected $data = null;

  public function __construct($username) {

    $this->username = str::slug(basename($username));

    // check if the account file exists
    if(!file_exists($this->file())) {
      throw new Exception('The user account could not be found');
    }

  }

  /**
   * Returns the username
   *
   * @return string
   */
  public function username() {
    return $this->username;
  }

  /**
   * get all data for the user
   */
  public function data() {

    if(!is_null($this->data)) return $this->data;

    // get all data from the account file
    $this->data = data::read($this->file(), 'yaml');

    // make sure all keys are lowercase
    $this->data = array_change_key_case($this->data, CASE_LOWER);

    // remove garbage
    unset($this->data[0]);

    // add the username
    $this->data['username'] = $this->username;

    // return the data array
    return $this->data;

  }

  public function __get($key) {
    return a::get($this->data(), strtolower($key));
  }

  public function __call($key, $arguments = null) {
    return $this->__get($key);
  }

  public function role() {

    $roles = kirby::instance()->site()->roles();
    $data  = $this->data();

    if(empty($data['role'])) {
      // apply the fallback "nobody" role if no role is stored for the user
      $data['role'] = 'nobody';
    }

    // return the role by id
    if($role = $roles->get($data['role'])) {
      return $role;
    } else {
      // return the fallback "nobody" role without permissions
      return $roles->get('nobody');
    }

  }

  public function hasRole() {
    $roles = func_get_args();
    return in_array($this->role()->id(), $roles);
  }

  // support for old 'panel' role permission
  public function hasPanelAccess() {
    return $this->can('panel.access');
  }

  /**
   * Checks if the user has permission for the specified event
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the permission callbacks
   * @return Obj Object with status() and message() methods
   */
  public function permission($event, $args = []) {
    if(is_string($event)) {
      $event = new Kirby\Event($event);
    } else if(!is_a($event, 'Kirby\\Event')) {
      throw new Error('Invalid event.');
    }

    // make sure that the user is set correctly
    $event->user = $this;

    return $this->role()->permission($event, $args);
  }

  /**
   * Returns true if the user has permission for the specified event
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the permission callbacks
   * @return boolean
   */
  public function can($event, $args = []) {
    return $this->permission($event, $args)->status() === true;
  }

  /**
   * Returns true if the user has *no* permission for the specified event
   *
   * @param Event $event Event object or a string with the event name
   * @param mixed $args Additional arguments for the permission callbacks
   * @return boolean
   */
  public function cannot($event, $args = []) {
    return $this->permission($event, $args)->status() === false;
  }

  public function isAdmin() {
    return $this->role()->id() === 'admin';
  }

  public function avatar() {

    if(isset($this->cache['avatar'])) return $this->cache['avatar'];

    $avatar = new Avatar($this);

    return $this->cache['avatar'] = $avatar->exists() ? $avatar : false;

  }

  public function avatarRoot($extension = 'jpg') {
    return kirby::instance()->roots()->avatars() . DS . $this->username() . '.' . $extension;
  }

  public function gravatar($size = 256) {
    if(!$this->email()) return false;
    return gravatar($this->email(), $size);
  }

  protected function file() {
    return kirby::instance()->roots()->accounts() . DS . $this->username() . '.php';
  }

  public function textfile() {
    return $this->file();
  }

  public function exists() {
    return file_exists($this->file());
  }

  public function generateKey() {
    return str::random(64);
  }

  public function generateSecret($key) {
    return sha1($this->username() . $key);
  }

  /**
   * Log in with password
   *
   * @param  string $password
   * @return boolean
   */
  public function login($password) {

    if(!$this->password) return false;
    if(!password::match($password, $this->password)) return false;

    return $this->_login();

  }

  /**
   * Log in without password
   *
   * @return boolean
   */
  public function loginPasswordless() {

    return $this->_login();

  }

  /**
   * Processes the successful login
   *
   * @return boolean
   */
  protected function _login() {

    $data = array();
    if(static::current()) {
      // logout active users first
      static::logout();
      
      // don't preserve current session data
      // because of privilege level change
    } else {
      // get all the current session data
      $data = s::get();

      // remove anything kirby related from 
      // the current session data
      foreach($data as $key => $value) {
        if(str::startsWith($key, 'kirby_')) {
          unset($data[$key]);
        }
      }
    }

    // start a new session with a new session ID
    s::restart();
    s::regenerateId();

    // copy over the old session stuff
    s::set($data);

    $key    = $this->generateKey();
    $secret = $this->generateSecret($key);

    s::set('kirby_auth_secret', $secret);
    s::set('kirby_auth_username', $this->username());

    cookie::set(
      s::$name . '_auth', 
      $key, 
      s::$cookie['lifetime'], 
      s::$cookie['path'], 
      s::$cookie['domain'], 
      s::$cookie['secure'], 
      s::$cookie['httponly']
    );

    return true;

  }

  static public function logout() {

    s::destroy();    

    // remove the session cookie
    cookie::remove(s::$name . '_auth');

  }

  public function is($user) {
    if(!is_a($user, 'User')) return false;
    return $this->username() === $user->username();
  }

  public function isCurrent() {
    return $this->is(static::current());
  }

  static public function validate($data = array(), $mode = 'insert') {

    if($mode == 'insert') {

      if(empty($data['username'])) {
        throw new Exception('Invalid username');
      }

    }

  }

  public function update($data = array()) {

    // sanitize the given data
    $data = $this->sanitize($data, 'update');

    // validate the updated dataset
    $this->validate($data, 'update');

    // don't update the username
    unset($data['username']);

    // create a new hash for the password
    if(!empty($data['password'])) {
      $data['password'] = password::hash($data['password']);
    }

    // merge with existing fields
    $this->data = array_merge($this->data(), $data);

    foreach($this->data as $key => $value) {
      if(is_null($value)) unset($this->data[$key]);
    }

    // save the new user data
    static::save($this->file(), $this->data);

    // return the updated user project
    return $this;

  }

  public function delete() {

    if($avatar = $this->avatar()) {
      $avatar->delete();
    }

    if(!f::remove($this->file())) {
      throw new Exception('The account could not be deleted');
    } else {
      return true;
    }

  }

  static public function sanitize($data, $mode = 'insert') {

    // all usernames must be lowercase
    $data['username'] = str::slug(a::get($data, 'username'));

    // convert all keys to lowercase
    $data = array_change_key_case($data, CASE_LOWER);

    // return the cleaned up data
    return $data;

  }

  /**
   * Creates a new user
   *
   * @param array $user
   * @return User
   */
  static public function create($data = array()) {

    // sanitize the given data for the new user
    $data = static::sanitize($data, 'insert');

    // validate the dataset
    static::validate($data, 'insert');

    // create the file root
    $file = kirby::instance()->roots()->accounts() . DS . $data['username'] . '.php';

    // check for an existing username
    if(file_exists($file)) {
      throw new Exception('The username is taken');
    }

    // create a new hash for the password
    if(!empty($data['password'])) {
      $data['password'] = password::hash($data['password']);
    }

    static::save($file, $data);

    // return the created user project
    return new static($data['username']);

  }

  static protected function save($file, $data) {

    $yaml  = '<?php if(!defined(\'KIRBY\')) exit ?>' . PHP_EOL . PHP_EOL;
    $yaml .= data::encode($data, 'yaml');

    if(!f::write($file, $yaml)) {
      throw new Exception('The user account could not be saved');
    } else {
      return true;
    }

  }

  static public function unauthorize() {

    s::remove('kirby_auth_secret');
    s::remove('kirby_auth_username');

    cookie::remove('kirby_auth');

  }

  static public function current() {

    $cookey   = cookie::get(s::$name . '_auth'); 
    $username = s::get('kirby_auth_username'); 

    if(empty($cookey)) {
      static::unauthorize();
      return false;
    }

    if(s::get('kirby_auth_secret') !== sha1($username . $cookey)) {
      static::unauthorize();
      return false;
    }

    // find the logged in user by token
    try {
      $user = new static($username);
      return $user;
    } catch(Exception $e) {
      static::unauthorize();
      return false;
    }

  }

  /**
   * Converts the user object to an array
   * 
   * @return array
   */
  public function toArray() {
    return [
      'username'  => $this->username(),
      'email'     => $this->email(),
      'role'      => $this->role()->id(),
      'language'  => $this->language(),
      'avatar'    => $this->avatar() ? $this->avatar()->url() : false,
      'gravatar'  => $this->gravatar(),
      'isCurrent' => $this->isCurrent()
    ];
  }

  public function __toString() {
    return (string)$this->username;
  }

  /**
   * Improved @var_dump output
   * 
   * @return array
   */
  public function __debuginfo() {
    return $this->toArray();
  }

  public function __clone() {
    if(isset($this->cache['avatar'])) $this->cache['avatar'] = clone $this->cache['avatar'];
  }

}