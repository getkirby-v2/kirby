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

  public function __construct($field, $page = null) {

    if(is_a($field, 'Field')) {
      $this->field = $field;
    } else if(is_array($field)) {
      throw new Exception('Kirbytext cannot handle arrays');
    } else if(empty($field) or is_string($field)) {
      if(!$page) $page = page();
      $this->field = new Field($page, null, $field);
    }

  }

  public function field() {
    return $this->field;
  }

  public function parse() {

    if(!$this->field) return '';

    $text = $this->field->value;

    // pre filters
    foreach(static::$pre as $filter) {
      $text = call_user_func_array($filter, array($this, $text));
    }

    // tagsify
    $text = preg_replace_callback('!(?=[^\]])\([a-z0-9_-]+:.*?\)!is', array($this, 'tag'), $text);

    // markdownify
    $text = kirby::instance()->component('markdown')->parse($text);

    // smartypantsify
    $text = kirby::instance()->component('smartypants')->parse($text);

    // post filters
    foreach(static::$post as $filter) {
      $text = call_user_func_array($filter, array($this, $text));
    }

    return $text;

  }

  public function tag($input) {

    // remove the brackets
    $tag  = trim(rtrim(ltrim($input[0], '('), ')'));
    $name = trim(substr($tag, 0, strpos($tag, ':')));

    // if the tag is not installed return the entire string
    if(!isset(static::$tags[$name])) return $input[0];

    try {
      $tag = new Kirbytag($this, $name, $tag);
      return $tag->html();
    } catch(Exception $e) {
      // broken tags will be ignored
      return $input[0];
    }

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
    try {
      return $this->parse();
    } catch(Exception $e) {
      // on massive render bugs the entire text will be returned
      return $this->field->value;
    }
  }

}