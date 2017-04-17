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
    $text = $this->parseTags(array($this, 'tag'), $text);

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

  public function parseTags($callback, $input) {

    $res = "";

    $stateNormal = 'normal';
    $stateInParentheses = 'inparentheses';
    $stateInTag = 'intag';

    $state = $stateNormal;
    $sub = "";
    $openingBracketsCount = 0;

    for ($i = 0; $i < strlen($input); $i++) {

      switch($state) {
        case $stateNormal:

          // if we find an opening bracket, the we go to the `InParentheses` state
          if ($input[$i] == "(") {
            $state = $stateInParentheses;
            $sub .= $input[$i];
            break;
          }

          $res .= $input[$i];
          break;

        case $stateInParentheses:

          // if we see another opening bracket, then we start all over again in the `InParentheses` state
          if ($input[$i] == '(') {
            $res .= $sub;
            $sub = $input[$i];
            break;
          }

          // if we see a colon, then we are in a tag for sure, so we can start counting opening/closing brackets
          if ($input[$i] == ':') {
            $state = $stateInTag;
            $sub .= $input[$i];
            break;
          }

          // if we have an other char than a-zA-Z0-9-_ then we go to normal state
          if ($this->isAZ09($input[$i]) == false) {
            $state = $stateNormal;
            $res .= $sub . $input[$i];
            $sub = '';
            break;
          }

          $sub .= $input[$i];
          break;

        case $stateInTag:

          $sub .= $input[$i];

          if ($input[$i] == '(') {
            $openingBracketsCount++;
          } else if ($input[$i] == ')') {
            $openingBracketsCount--;
          }

          // last closing bracket, so the tag is complete
          if ($openingBracketsCount == -1) {

            $temp = call_user_func($callback, array($sub));
            $res = $res . $temp;

            $sub = '';

            $state = $stateNormal;
            $openingBracketsCount = 0;

            break;
          }

          break;
      }
    }

    return $res;
  }

  public function isAZ09($char) {
    if ($char >= '0' && $char <= '9'
        || $char >= 'A' && $char <= 'Z'
        || $char >= 'a' && $char <= 'z'
        || $char == '-'
        || $char == '_') {

      return true;
    } else {
      return false;
    }
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