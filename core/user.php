<?php

/**
 * Users
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

    $this->username = str::lower($username);

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
      // apply the default role, if no role is stored for the user
      $data['role'] = $roles->findDefault()->id();
    }

    // return the role by id
    if($role = $roles->get($data['role'])) {
      return $role;
    } else {
      return $roles->findDefault();
    }

  }

  public function hasRole() {
    $roles = func_get_args();
    return in_array($this->role()->id(), $roles);
  }

  public function hasPanelAccess() {
    return $this->role()->hasPanelAccess();
  }

  public function isAdmin() {
    return $this->role()->id() == 'admin';
  }

  public function avatar() {

    if(isset($this->cache['avatar'])) return $this->cache['avatar'];

    // allowed extensions
    $extensions = array('jpg', 'jpeg', 'png', 'gif');

    // try to find the avatar
    $root = kirby::instance()->roots()->avatars() . DS . $this->username();

    foreach($extensions as $ext) {
      $file = $root . '.' . $ext;
      if(file_exists($file)) {
        return $this->cache['avatar'] = new Media($file, kirby::instance()->urls()->avatars() . '/' . f::filename($file));
      }
    }

    return $this->cache['avatar'] = false;

  }

  public function avatarRoot($extension = 'jpg') {
    return kirby::instance()->roots()->avatars() . DS . $this->username() . '.' . $extension;
  }

  public function gravatar($size = 256) {
    return gravatar($this->email(), $size);
  }

  protected function file() {
    return kirby::instance()->roots()->accounts() . DS . $this->username() . '.php';
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

  public function login($password) {

    static::logout();

    if(!password::match($password, $this->password)) return false;

    $key    = $this->generateKey();
    $secret = $this->generateSecret($key);

    // http only cookie
    cookie::set('kirby', $key, 0, '/', null, false, true);

    s::set('auth.created', time());
    s::set('auth.updated', time());
    s::set('auth.key', $key);
    s::set('auth.secret', $secret);
    s::set('auth.username', $this->username());
    s::set('auth.ip', visitor::ip());
    s::set('auth.ua', visitor::ua());

    return true;

  }

  static public function logout() {

    session_regenerate_id();

    s::remove('auth.created');
    s::remove('auth.updated');
    s::remove('auth.key');
    s::remove('auth.secret');
    s::remove('auth.username');
    s::remove('auth.ip');
    s::remove('auth.ua');
    
    cookie::remove('key');

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

      if(empty($data['password'])) {
        throw new Exception('Invalid password');
      }

    }

    if(!empty($data['email']) and !v::email($data['email'])) {
      throw new Exception('Invalid email');
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

  static public function current() {

    $cookey   = cookie::get('kirby'); 
    $username = s::get('auth.username'); 

    if(empty($cookey) or $cookey !== s::get('auth.key')) {
      static::logout();
      return false;
    }

    if(s::get('auth.secret') !== sha1($username . $cookey)) {
      static::logout();
      return false;
    }

    if(s::get('auth.ua') !== visitor::ua()) {
      static::logout();
      return false;
    }

    // keep logged in for one week max.
    if(s::get('auth.created') < time() - (60 * 60 * 24 * 7)) {
      static::logout();
      return false;
    }

    // find the logged in user by token
    if($user = site()->user($username)) {
      return $user;
    } else {
      return false;
    }

  }

  public function __toString() {
    return (string)$this->username;
  }

}