<?php

/**
 * Embeds a snippet from the snippet folder
 *
 * @param string $file
 * @param mixed $data array or object
 * @param boolean $return
 * @return string
 */
function snippet($file, $data = array(), $return = false) {
  if(is_object($data)) $data = array('item' => $data);
  return tpl::load(c::get('root.snippets') . DS . $file . '.php', $data, $return);
}

/**
 * Builds a css link tag for relative or absolute urls
 *
 * @param string $url
 * @param string $media
 * @return string
 */
function css($url, $media = null) {

  if(is_array($url)) {
    $css = array();
    foreach($url as $u) $css[] = css($u);
    return implode(PHP_EOL, $css) . PHP_EOL;
  }

  // auto template css files
  if($url == '@auto') {

    $site = kirby::site();
    $file = $site->page()->template() . '.css';
    $root = $site->options['auto.css.root'] . DS . $file;
    $url  = $site->options['auto.css.url'] . '/' . $file;

    if(!file_exists($root)) return false;

  }

  return html::tag('link', null, array(
    'rel'   => 'stylesheet',
    'href'  => url($url),
    'media' => $media
  ));

}

/**
 * Builds a script tag for relative or absolute links
 *
 * @param string $src
 * @param boolean $async
 * @return string
 */
function js($src, $async = false) {

  if(is_array($src)) {
    $js = array();
    foreach($src as $s) $js[] = js($s);
    return implode(PHP_EOL, $js) . PHP_EOL;
  }

  // auto template css files
  if($src == '@auto') {

    $site = kirby::site();
    $file = $site->page()->template() . '.js';
    $root = $site->options['auto.js.root'] . DS . $file;
    $src  = $site->options['auto.js.url'] . '/' . $file;

    if(!file_exists($root)) return false;

  }

  return html::tag('script', '', array(
    'src'   => url($src),
    'async' => $async
  ));

}

/**
 * Global markdown parser shortcut
 *
 * @param string $text
 * @return string
 */
function markdown($text) {

  if(!c::get('markdown')) return $text;

  // markdown
  $parsedown = c::get('markdown.extra') ? new ParsedownExtra() : new Parsedown();

  // markdown auto-breaks
  if(c::get('markdown.breaks')) {
    $parsedown->setBreaksEnabled(true);
  }

  // parse it, baby!
  return $parsedown->text($text);

}

/**
 * Converts a string to Kirbytext
 *
 * @param Field $field
 * @return string
 */
function kirbytext($field) {
  return (string)new Kirbytext($field);
}

/**
 * Returns the site object
 *
 * @return Site
 */
function site() {
  return kirby::site();
}

/**
 * Returns either the current page or any page for a given uri
 *
 * @return Page
 */
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

/**
 * Renders a kirbytag
 *
 * @param array $attr
 * @return Kirbytag
 */
function kirbytag($attr) {
  return new Kirbytag(null, key($attr), $attr);
}

/**
 * Builds a Youtube video iframe
 *
 * @param string $url
 * @param mixed $width
 * @param mixed $height
 * @param string $class
 * @return string
 */
function youtube($url, $width = null, $height = null, $class = null) {
  return kirbytag(array(
    'youtube' => $url,
    'width'   => $width,
    'height'  => $height,
    'class'   => $class
  ));
}

/**
 * Builds a Vimeo video iframe
 *
 * @param string $url
 * @param mixed $width
 * @param mixed $height
 * @param string $class
 * @return string
 */
function vimeo($url, $width = null, $height = null, $class = null) {
  return kirbytag(array(
    'vimeo'   => $url,
    'width'   => $width,
    'height'  => $height,
    'class'   => $class
  ));
}

/**
 * Builds a Twitter link
 *
 * @param string $username
 * @param string $text
 * @param string $title
 * @param string $class
 * @return string
 */
function twitter($username, $text = null, $title = null, $class = null) {
  return kirbytag(array(
    'twitter' => $username,
    'text'    => $text,
    'title'   => $title,
    'class'   => $class
  ));
}

/**
 * Embeds a Github Gist
 *
 * @param string $url
 * @param string $file
 * @return string
 */
function gist($url, $file = null) {
  return kirbytag(array(
    'gist' => $url,
    'file' => $file,
  ));
}