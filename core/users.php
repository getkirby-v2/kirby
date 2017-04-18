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
abstract class UsersAbstract extends Collection {

  public function __construct() {

    $root = kirby::instance()->roots()->accounts();

    foreach(dir::read($root) as $file) {

      // skip invalid account files
      if(!in_array(f::extension($file), array('yml', 'php', 'yaml'))) continue;

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

  /**
   * Improved @var_dump output
   * 
   * @return array
   */
  public function __debuginfo() {
    return array_keys($this->data);
  }

}