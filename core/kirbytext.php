<?php

/**
 * Kirbytext
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
abstract class KirbytextAbstract {

  static public $tags = array();
  static public $pre  = array();
  static public $post = array();

  public $field;

  public function __construct($field) {

    if(empty($field) or is_string($field)) {
      $value = $field;
      $field = new Field();
      $field->value = $value;
      $field->page  = page();
    }

    $this->field = $field;

  }

  public function field() {
    return $this->field;
  }

  public function parse() {

    if(!$this->field) return '';

    $val = $this->field->value;

    // pre filters
    foreach(static::$pre as $filter) {
      $val = call_user_func_array($filter, array($this, $val));
    }

    // tags
    $val = preg_replace_callback('!(?=[^\]])\(([a-z0-9]+:(?:\\\\\)|[^\)])*?)\)!i', array($this, 'tag'), $val);

    // markdownify
    $val = markdown($val);

    // post filters
    foreach(static::$post as $filter) {
      $val = call_user_func_array($filter, array($this, $val));
    }

    return $val;

  }

  public function tag($input) {

    $tag  = trim($input[1]);
    $name = trim(substr($tag, 0, strpos($tag, ':')));

    // if the tag is not installed return the entire string
    if(!isset(static::$tags[$name])) return $input[0];

    $tag = new Kirbytag($this, $name, $tag);

    return $tag->html();

  }

  static public function install($root) {

    if(!is_dir($root)) return false;

    foreach(scandir($root) as $file) {
      if(pathinfo($file, PATHINFO_EXTENSION) == 'php') {
        $name = pathinfo($file, PATHINFO_FILENAME);
        $tag  = include($root . DS . $file);
        if(is_array($tag)) Kirbytext::$tags[$name] = $tag;
      }
    }

  }

  public function __toString() {
    return $this->parse();
  }

}