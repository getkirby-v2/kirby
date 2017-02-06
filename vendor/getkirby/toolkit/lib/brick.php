<?php

class Brick {

  public static $bricks = array();

  public $tag    = null;
  public $attr   = array();
  public $html   = null;
  public $events = array();

  public function __construct($tag, $html = false, $attr = array()) {

    if(is_array($html)) {
      $attr = $html;
      $html = false;
    }

    $this->tag($tag);
    $this->html($html);
    $this->attr($attr);

  }

  public function __set($attr, $value) {
    $this->attr($attr, $value);
  }

  public function on($event, $callback) {
    if(!isset($this->events[$event])) $this->events[$event] = array();
    $this->events[$event][] = $callback;
    return $this;
  }

  public function trigger($event, $args = array()) {
    if(isset($this->events[$event])) {
      array_unshift($args, $this);
      foreach($this->events[$event] as $e) {
        call_user_func_array($e, $args);
      }
    }
  }

  public function tag($tag = null) {
    if(is_null($tag)) return $this->tag;
    $this->tag = $tag;
    return $this;
  }

  public function attr($key = null, $value = null) {
    if(is_null($key)) {
      return $this->attr;
    } else if(is_array($key)) {
      foreach($key as $k => $v) {
        $this->attr($k, $v);
      }
      return $this;
    } else if(is_null($value)) {
      return a::get($this->attr, $key);
    } else if($key == 'class') {
      $this->addClass($value);
      return $this;
    } else {
      $this->attr[$key] = $value;
      return $this;
    }
  }

  public function data($key = null, $value = null) {
    if(is_null($key)) {
      $data = array();
      foreach($this->attr as $key => $val) {
        if(str::startsWith($key, 'data-')) {
          $data[$key] = $val;
        }
      }
      return $data;
    } else if(is_array($key)) {
      foreach($key as $k => $v) {
        $this->data($k, $v);
      }
      return $this;
    } else if(is_null($value)) {
      return a::get($this->attr, 'data-' . $key);
    } else {
      $this->attr['data-' . $key] = $value;
      return $this;
    }
  }

  public function removeAttr($key) {
    unset($this->attr[$key]);
    return $this;
  }

  public function classNames() {

    if(!isset($this->attr['class'])) {
      $this->attr['class'] = array();
    } else if(is_string($this->attr['class'])) {
      $raw = $this->attr['class'];
      $this->attr['class'] = array();
      $this->addClass($raw);
    }

    return $this->attr['class'];

  }

  public function val($value = null) {
    return $this->attr('value', $value);
  }

  public function addClass($class) {

    $classNames = $this->classNames();
    $classIndex = array_map('strtolower', $classNames);

    foreach(str::split($class, ' ') as $c) {
      if(!in_array(strtolower($c), $classIndex)) {
        $classNames[] = $c;
      }
    }

    $this->attr['class'] = $classNames;

    return $this;

  }

  public function removeClass($class) {

    $classNames = $this->classNames();

    foreach(str::split($class, ' ') as $c) {
      $classNames = array_filter($classNames, function($e) use($c) {
        return (strtolower($e) !== strtolower($c));
      });
    }

    $this->attr['class'] = $classNames;

    return $this;

  }

  public function replaceClass($classA, $classB) {
    return $this->removeClass($classA)->addClass($classB);
  }

  public function text($text = null) {
    if(is_null($text)) return trim(strip_tags($this->html));
    $this->html = html($text, false);
    return $this;
  }

  public function html($html = null) {
    if(is_null($html)) {
      return $this->html = $this->isVoid() ? null : $this->html;
    }
    $this->html = $html;
    return $this;
  }

  public function prepend($html) {
    if(is_callable($html)) $html = $html();
    $this->html = $html . $this->html;
    return $this;
  }

  public function append($html) {
    if(is_callable($html)) $html = $html();
    $this->html = $this->html . $html;
    return $this;
  }

  public function isVoid() {
    return html::isVoid($this->tag());
  }

  public function toString() {
    $this->attr['class'] = implode(' ', $this->classNames());
    return html::tag($this->tag(), $this->html(), $this->attr());
  }

  public function __toString() {
    try {
      return $this->toString();
    } catch(Exception $e) {
      return 'Error: ' . $e->getMessage();
    }
  }

  public static function make($id, $callback) {
    static::$bricks[$id] = $callback;
  }

  public static function get($id) {
    if(!isset(static::$bricks[$id])) return false;
    $args = array_slice(func_get_args(), 1);
    return call_user_func_array(static::$bricks[$id], $args);
  }

}
