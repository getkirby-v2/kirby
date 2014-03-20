<?php 

class UsersAbstract extends Collection {

  public function __construct() {

    $root = c::get('root.accounts');

    foreach(dir::read($root) as $file) {

      // skip invalid account files
      if(f::extension($file) != 'php') continue;

      $user = new User(f::name($file));
      $this->append($user->username(), $user);

    }
      
  }

  public function create($data) {
    return user::create($data);
  }

  public function find($username) {
    return $this->findBy('username', $username);
  }

}