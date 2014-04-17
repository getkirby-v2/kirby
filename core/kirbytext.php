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

    // tags
    $val = preg_replace_callback('!(?=[^\]])\([a-z0-9]+:.*?\)!i', array($this, 'tag'), $val);

    // unwrap single images, which are wrapped with p elements
    $val = preg_replace('!\<p>(<img.*?\/>)<\/p>!', '$1', $val);

    // markdown
    $pd = Parsedown::instance();
    $pd->setBreaksEnabled(true);

    $val = $pd->parse($val);

    return $val;

  }

  public function tag($input) {

    // remove the brackets
    $tag  = trim(rtrim(ltrim($input[0], '('), ')'));
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