<?php

/**
 * Password
 *
 * Password encryption class
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Password {


  /**
   * Generates a salted hash for a plaintext password
   *
   * @param string $plaintext
   * @return string
   */
  public static function hash($plaintext) {
    return password_hash($plaintext, PASSWORD_DEFAULT);
  }

  /**
   * Checks if a given string is already a hash
   *
   * @param string
   * @return boolean
   */
  public static function isHash($hash) {
    return password_get_info($hash)['algo'] !== 0;
  }

  /**
   * Checks if a password is still hashed
   * with the old crypt method
   *
   * @param string $hash
   * @return boolean
   */
  public static function isCryptHash($hash) {
    return preg_match('!^\$2a\$10\$!', $hash) === 1 ? true : false;
  }

  /**
   * Checks if a password matches the encrypted hash
   *
   * @param string $plaintext
   * @param string $hash
   * @return boolean
   */
  public static function match($plaintext, $hash) {

    if (static::isCryptHash($hash) === true) {
      return hash_equals(crypt($plaintext, $hash), $hash);
    }

    return password_verify($plaintext, $hash) === true;
  }

}
