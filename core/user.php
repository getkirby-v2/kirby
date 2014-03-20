<?php 

class UserAbstract {

  protected $data  = array();
  protected $cache = array();

  public function __construct($username) {

    $this->data['username'] = $username;    

    // check if the account file exists
    if(!file_exists($this->file())) {
      throw new Exception('The user account could not be found');
    }

    // get all data for the user
    $this->data = data::read($this->file(), 'yaml');

    // remove garbage
    unset($this->data[0]);

  }

  public function __get($key) {
    return a::get($this->data, $key);
  }

  public function __call($key, $arguments = null) {
    return $this->$key;
  }

  public function avatar() {

    if(isset($this->cache['avatar'])) return $this->cache['avatar'];

    // try to find the avatar
    $root = c::get('root') . DS . 'assets' . DS . 'avatars' . DS . $this->username() . '.{jpg,png}';
    
    if($avatar = a::first(glob($root, GLOB_BRACE))) {
      return $this->cache['avatar'] = new Media($avatar, url('assets/avatars/' . f::filename($avatar)));
    } else {
      return $this->cache['avatar'] = false;
    }

  }

  public function gravatar($size = 256) {
    return gravatar($this->email(), $size);
  }

  protected function file() {
    return c::get('root.accounts') . DS . $this->data['username'] . '.php';
  }

  public function login($password) {
    if(!password::match($password, $this->password)) return false;

    $token = $this->generateToken();
    $key   = $this->generateKey($token);
    
    $this->update(array(
      'token' => $token
    ));

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
    return sha1($this->username()) . '|' . time() . '|' . $token;
  }

  public function is($user) {
    if(!is_a($user, 'User')) return false;
    return $this->username() === $user->username();
  }

  public function isCurrent() {
    return $this->is(static::current());
  }

  public function update($data = array()) {

    if(!empty($data['email']) and !v::email($data['email'])) {
      throw new Exception('Invalid email');
    }

    if(!empty($data['password'])) {
      $data['password'] = password::hash($data['password']);
    }

    // merge with existing fields
    $data = array_merge($this->data, $data);

    foreach($data as $key => $value) {
      if(is_null($value)) unset($data[$key]);
    }

    // save the new user data
    static::save($this->file(), $data);

    // return the updated user project
    return new static($this->username());

  }

  public function delete() {

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

    if(empty($data['username'])) {
      throw new Exception('Invalid username');
    }

    if(empty($data['password'])) {
      throw new Exception('Invalid password');
    }

    if(empty($data['email']) or !v::email($data['email'])) {
      throw new Exception('Invalid email address');
    }

    $file = c::get('root.accounts') . DS . $data['username'] . '.php';

    if(file_exists($file)) {
      throw new Exception('The username is taken');
    }

    // encrypt the password
    $data['password'] = password::hash($data['password']);    

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
    if(count($parts) != 3) return false;

    $hash  = $parts[0];
    $time  = $parts[1];
    $token = $parts[2];

    // keep logged in for one week
    if($time < time() - (60 * 60 * 24 * 7)) return false;

    // find the logged in user by token
    $user = site()->users()->findBy('token', $token);

    if(!$user) return false;

    // compare the hash as a last check
    if(sha1($user->username()) != $hash) {
      $user->logout();
      return false;
    }

    return $user;

  }

}