<?php

/**
 * Error
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Error extends Exception {

  public function message() {
    return $this->message;
  }

  public function code() {
    return $this->code;
  }

  public function __toString() {
    return $this->message;
  }

}