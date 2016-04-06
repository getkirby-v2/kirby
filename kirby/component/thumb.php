<?php

namespace Kirby\Component;

use A;
use F;
use File;
use R;
use Redirect;
use Obj;

/**
 * Kirby Thumb Render and API Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Thumb extends \Kirby\Component {

  /**
   * Returns the default options for the thumb component
   * 
   * @return array
   */  
  public function defaults() {
    return [
      'thumbs.driver'    => 'gd',
      'thumbs.bin'       => 'convert',
      'thumbs.interlace' => false,
      'thumbs.quality'   => 90,
      'thumbs.memory'    => '128M'
    ];    
  }

  /**
   * Configures the thumb driver
   */
  public function configure() {

    $self = $this;    

    // setup the thumbnail location
    \thumb::$defaults['root'] = $this->kirby->roots->thumbs();
    \thumb::$defaults['url']  = $this->kirby->urls->thumbs();

    // setup the default thumbnail options
    \thumb::$defaults['driver']    = $this->kirby->options['thumbs.driver'];
    \thumb::$defaults['bin']       = $this->kirby->options['thumbs.bin'];
    \thumb::$defaults['quality']   = $this->kirby->options['thumbs.quality'];
    \thumb::$defaults['interlace'] = $this->kirby->options['thumbs.interlace'];
    \thumb::$defaults['memory']    = $this->kirby->options['thumbs.memory'];

    // setup the destination creator
    \thumb::$defaults['destination'] = function($thumb) use($self) {

      $path = $self->path($thumb->source, $thumb->options);

      return new Obj([
        'root' => $self->kirby->roots()->thumbs() . DS . str_replace('/', DS, $path),
        'url'  => $self->kirby->urls()->thumbs() . '/' . $path,
      ]);

    };

  }

  /**
   * Thumb API Handler
   * 
   * This is being called by the thumb api route in 
   * order to create thumbnails on the fly for files like
   * 
   * http://yourdomain/some/page/myimage.jpg?w=200&h=200
   * 
   * @param Page $page
   * @param File $file
   * @param array $args
   */
  public function api($page, $file, $args) {

    $thumb = new \Thumb($file, $this->args($args));

    // redirect to the generated thumbnail
    go($thumb->url());

  }

  /**
   * Checks if the file is compatible with the
   * thumbs driver
   * 
   * @param File $file
   * @return boolean
   */
  public function isCompatible(File $file) {
    return in_array($file->extension(), ['jpg', 'jpeg', 'gif', 'png']);
  }

  /**
   * URL builder for resized/modified files
   * 
   * @param File $file
   * @return string
   */
  public function url(File $file) {

    // get all modifications for the file
    $modifications = $file->modifications();

    // don't try to create a API url for files without the option to generate previews
    if(empty($modifications) || !$this->isCompatible($file)) {
      return $file->page()->contentUrl() . '/' . rawurlencode($file->filename());
    }

    // build the thumb query
    $query    = $this->query($modifications);
    $path     = $this->path($file, $query);
    $root     = $this->kirby()->roots()->thumbs() . DS . str_replace('/', DS, $path);
    $modified = $file->modified();

    if(file_exists($root) and filemtime($root) >= $modified) {
      return $this->kirby()->urls()->thumbs() . '/' . $path;
    } 

    $filename = rawurlencode($file->filename()) . '?' . $query;

    if($file->page()->isHomePage()) {
      return $file->page()->url() . '/' . $this->kirby()->option('home') . '/' . $filename;      
    } else {
      return $file->page()->url() . '/' . $filename;      
    }    

  }

  /**
   * Returns the clean path for a thumbnail
   * 
   * @param string $file
   * @param array $args
   * @return string
   */
  public function path($file, $args = []) {

    $page  = $file->page();
    $query = is_array($args) ? $this->query($args) : $args;
    $name  = str_replace('@', '-', f::safeName($file->name()));
    $path  = $page->id() . '/' . $name . r(!empty($query), '@') . $query . '.' . $file->extension();
    $path  = ltrim($path, '/');

    return $path;

  }

  /**
   * Translate the request query into arguments for 
   * the thumbnail class
   * 
   * @param array $args
   * @return array
   */
  protected function args($args) {
    return [
      'width'      => $this->getInt($args, 'w', null), 
      'height'     => $this->getInt($args, 'h', null),
      'quality'    => $this->getInt($args, 'q', 90),
      'grayscale'  => $this->getBool($args, 'bw'),
      'crop'       => $this->getBool($args, 'crop'),
      'blur'       => $this->getBool($args, 'blur'),
      'upscale'    => $this->getBool($args, 'upscale'),
      'autoOrient' => $this->getBool($args, 'autoOrient'),
      'interlace'  => $this->getBool($args, 'interlace'),
      'overwrite'  => true
    ];
  }

  /**
   * Returns a sanitized boolean argument from the query string
   * 
   * @param array $args
   * @param string $key
   * @param mixed $default
   * @return boolean
   */
  protected function getBool($args, $key, $default = null) {
    $value = a::get($args, $key, $default);
    return ($value === true || $value === 1 || $value === 'true' || $value === '1');
  }

  /**
   * Returns a sanitized integer argument from the query string
   * 
   * @param array $args
   * @param string $key
   * @param mixed $default
   * @return int
   */
  protected function getInt($args, $key, $default = null) {
    $value = a::get($args, $key, $default);
    return $value === null ? null : intval($value);    
  }

  /**
   * Converts the thumb args to query string
   * 
   * @param array $args
   * @return string
   */
  protected function query($args) {

    $keys = [
      'width'     => 'w', 
      'height'    => 'h', 
      'quality'   => 'q',
      'crop'      => 'crop',
      'blur'      => 'blur',
      'grayscale' => 'bw'
    ];

    $args  = array_merge(\thumb::$defaults, $args);
    $query = [];

    foreach($keys as $long => $short) {

      $value = a::get($args, $long);

      if(!empty($value)) {
        $query[$short] = $value;
      }

    }

    return http_build_query($query);

  }

}