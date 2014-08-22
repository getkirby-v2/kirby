<?php

class UserAbstract {

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
    return a::get($this->data(), $key);
  }

  public function __call($key, $arguments = null) {
    return $this->__get($key);
  }

  public function avatar() {

    if(isset($this->cache['avatar'])) return $this->cache['avatar'];

    // try to find the avatar
    $root = c::get('root') . DS . 'assets' . DS . 'avatars' . DS . $this->username() . '.{jpg,png}';

    if($avatar = a::first((array)glob($root, GLOB_BRACE))) {
      return $this->cache['avatar'] = new Media($avatar, url('assets/avatars/' . f::filename($avatar)));
    } else {
      return $this->cache['avatar'] = false;
    }

  }

  public function avatarRoot($extension = 'jpg') {
    return c::get('root') . DS . 'assets' . DS . 'avatars' . DS . $this->username() . '.' . $extension;
  }

  public function gravatar($size = 256) {
    return gravatar($this->email(), $size);
  }

  protected function file() {
    return c::get('root.accounts') . DS . $this->username() . '.php';
  }

  public function exists() {
    return file_exists($this->file());
  }

  public function login($password) {
    if(!password::match($password, $this->password)) return false;

    $token = $this->generateToken();
    $key   = $this->generateKey($token);

    try {
      $this->update(array(
        'token' => $token
      ));
    } catch(Exception $e) {
      throw new Exception('The authentication token could not be stored.');
    }

    cookie::set('key', $key);
    return true;

  }

  public function logout() {

    if($this->isCurrent()) {
      cookie::remove('key');
    }

    $this->update(array('token' => null));

  }

  public function generateToken() {
    return sha1($this->username() . str::random(32) . time());
  }

  public function generateKey($token) {
    return sha1($this->username()) . '|' . time() . '|' . $token . '|' . base64_encode($this->username());
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

  /**
   * Creates a new user
   *
   * @param array $user
   * @return User
   */
  static public function create($data = array()) {

    static::validate($data, 'insert');

    // all usernames must be lowercase
    $data['username'] = str::lower($data['username']);

    // create the file root
    $file = c::get('root.accounts') . DS . $data['username'] . '.php';

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

    $key = cookie::get('key');

    if(!$key) return false;

    $parts = str::split($key, '|');

    // make sure all three parts are there
    if(count($parts) != 4) return false;

    $hash     = $parts[0];
    $time     = $parts[1];
    $token    = $parts[2];
    $username = base64_decode($parts[3]);

    // keep logged in for one week
    if($time < time() - (60 * 60 * 24 * 7)) return false;

    // find the logged in user by token
    $user = site()->user($username);

    if(!$user) return false;

    // compare the token and the hash as a last check
    if($user->token() != $token or sha1($user->username()) != $hash) return false;

    return $user;

  }

  public function __toString() {
    return $this->username;
  }

}