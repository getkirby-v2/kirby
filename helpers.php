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
  return kirby::instance()->component('snippet')->render($file, $data, $return);
}

/**
 * Builds a css link tag for relative or absolute urls
 *
 * @param string $url
 * @param string $media
 * @return string
 */
function css() {
  return call([kirby::instance()->component('css'), 'tag'], func_get_args());
}

/**
 * Builds a script tag for relative or absolute links
 *
 * @param string $src
 * @param boolean $async
 * @return string
 */
function js($src, $async = false) {
  return call([kirby::instance()->component('js'), 'tag'], func_get_args());
}

/**
 * Global markdown parser shortcut
 *
 * @param string $text
 * @return string
 */
function markdown($text) {
  return kirby::instance()->component('markdown')->parse($text);
}

/**
 * Global smartypants parser shortcut
 *
 * @param string $text
 * @return string
 */
function smartypants($text) {
  return kirby::instance()->component('smartypants')->parse($text);
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
 * Returns the Kirby class singleton
 *
 * @return Kirby
 */
function kirby($class = null) {
  return kirby::instance($class);
}

/**
 * Returns the site object
 *
 * @return Site
 */
function site() {
  return kirby::instance()->site();
}

/**
 * Returns either the current page or any page for a given uri
 *
 * @return Page
 */
function page() {
  return call_user_func_array(array(kirby::instance()->site(), 'page'), func_get_args());
}

/**
 * Helper to build page collections
 *
 * @param array $data
 */
function pages($data = array()) {
  return new Pages($data);
}

/**
 * Creates an excerpt without html and kirbytext
 *
 * @param mixed $text Variable object or string
 * @param int $length The number of characters which should be included in the excerpt
 * @param array $params an array of options for kirbytext: array('markdown' => true, 'smartypants' => true)
 * @return string The shortened text
 */
function excerpt($text, $length = 140, $mode = 'chars') {

  if(strtolower($mode) == 'words') {
    $text = str::excerpt(kirbytext($text), 0);    

    if(str_word_count($text, 0) > $length) {
      $words = str_word_count($text, 2);
      $pos   = array_keys($words);
      $text  = str::substr($text, 0, $pos[$length]) . '...';
    }
    return $text;

  } else {
    return str::excerpt(kirbytext($text), $length);    
  }

}

/**
 * Helper to create correct text file names for content files
 *
 * @param string $uri
 * @param string $template
 * @param string $lang
 * @return string
 */
function textfile($uri, $template, $lang = null) {

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

  $uri  = ltrim($curi, '/');
  $root = kirby::instance()->roots()->content();
  $ext  = kirby::instance()->option('content.file.extension', 'txt');
  return $root . DS . r(!empty($uri), str_replace('/', DS, $uri) . DS) . $template . r($lang, '.' . $lang) . '.' . $ext;

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

/**
 * Returns the current url
 *
 * @return string
 */
function thisUrl() {
  return url::current();
}

/**
 * Give this any kind of array 
 * to get some kirby style structure
 * 
 * @param mixed $data
 * @param mixed $page
 * @param mixed $key
 * @return mixed
 */
function structure($data, $page = null, $key = null) {

  if(is_null($page)) {
    $page = page();
  }

  if(is_array($data)) {
    $result = new Structure();
    $result->page = $page;
    foreach($data as $key => $value) {
      $result->append($key, structure($value, $page, $key));
    }
    return $result;
  } else if(is_a($data, 'Field')) {
    return $data;
  } else {
    return new Field($page, $key, $data);
  } 

};


/**
 * Return an image from any page
 * specified by the path
 * 
 * Example: 
 * <?= image('some/page/myimage.jpg') ?>
 * 
 * @param string $path
 * @return File|null
 */
function image($path = null) {

  if($path === null) {
    return page()->image();
  }

  $uri      = dirname($path);
  $filename = basename($path);

  if($uri == '.') {
    $uri = null;
  }
  
  $page = $uri == '/' ? site() : page($uri);

  if($page) {
    return $page->image($filename);
  } else {
    return null;
  }

}

/**
 * Shortcut to create a new thumb object
 *
 * @param mixed Either a file path or a Media object
 * @param array An array of additional params for the thumb
 * @return object Thumb
 */
function thumb($image, $params = array(), $obj = true) {
  if(is_a($image, 'File') || is_a($image, 'Asset')) {
    return $obj ? $image->thumb($params) : $image->thumb($params)->url();
  } else {
    $class = new Thumb($image, $params);
    return $obj ? $class : $class->url();
  }
}