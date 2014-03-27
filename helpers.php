<?php

function snippet($file, $data = array(), $return = false) {
  return tpl::load(c::get('root.snippets') . DS . $file . '.php', $data, $return);
}

function css($url, $media = null) {

  if(is_array($url)) {
    $css = array();
    foreach($url as $u) $css[] = css($u);
    return implode(PHP_EOL, $css);
  }

  // auto template css files
  if($url == '@auto') {

    $site = kirby::site();
    $file = $site->page()->template() . '.css';
    $root = $site->options['root.auto.css'] . DS . $file;
    $url  = $site->options['url.auto.css'] . '/' . $file;

    if(!file_exists($root)) return false;

  }

  return html::tag('link', null, array(
    'rel'   => 'stylesheet',
    'href'  => url($url),
    'media' => $media
  ));

}

function js($src, $async = false) {

  if(is_array($src)) {
    $js = array();
    foreach($src as $s) $js[] = js($s);
    return implode(PHP_EOL, $js);
  }

  // auto template css files
  if($src == '@auto') {

    $site = kirby::site();
    $file = $site->page()->template() . '.js';
    $root = $site->options['root.auto.js'] . DS . $file;
    $src  = $site->options['url.auto.js'] . '/' . $file;

    if(!file_exists($root)) return false;

  }

  return html::tag('script', '', array(
    'src'   => url($src),
    'async' => $async
  ));

}

function kirbytext($field) {
  return (string)new Kirbytext($field);
}

function site() {
  return kirby::site();
}

function page() {
  return call_user_func_array(array(kirby::site(), 'page'), func_get_args());
}

/**
 * Creates an excerpt without html and kirbytext
 *
 * @param mixed $text Variable object or string
 * @param int $length The number of characters which should be included in the excerpt
 * @param array $params an array of options for kirbytext: array('markdown' => true, 'smartypants' => true)
 * @return string The shortened text
 */
function excerpt($text, $length = 140) {
  return str::excerpt(kirbytext($text), $length);
}

/**
 * Helper to create correct text file names for content files
 *
 * @param string $uri
 * @param string $template
 * @param string $lang
 * @return string
 */
function textfile($uri, $template = null, $lang = null) {
  if(is_null($template)) $template = $this->intendedTemplate();

  $curi   = '';
  $parts  = str::split($uri, '/');
  $parent = site();

  foreach($parts as $p) {

    if($parent and $child = $parent->children()->find($p)) {
      $curi  .= '/' . $child->dirname();
      $parent = $child;
    } else {
      $curi .= '/' . $p;
      $parent = null;
    }

  }

  $uri = ltrim($curi, '/');
  return c::get('root.content') . DS . r(!empty($uri), str_replace('/', DS, $uri) . DS) . $template . r($lang, '.' . $lang) . '.' . c::get('content.file.extension', 'txt');

}

function kirbytag($attr) {
  return new Kirbytag(null, key($attr), $attr);
}

function youtube($url, $width = null, $height = null, $class = null) {
  return kirbytag(array(
    'youtube' => $url,
    'width'   => $width,
    'height'  => $height,
    'class'   => $class
  ));
}

function vimeo($url, $width = null, $height = null, $class = null) {
  return kirbytag(array(
    'vimeo'   => $url,
    'width'   => $width,
    'height'  => $height,
    'class'   => $class
  ));
}

function twitter($username, $text = null, $title = null, $class = null) {
  return kirbytag(array(
    'twitter' => $username,
    'text'    => $text,
    'title'   => $title,
    'class'   => $class
  ));
}

function gist($url, $file = null) {
  return kirbytag(array(
    'gist' => $url,
    'file' => $file,
  ));
}