<?php

namespace Kirby\Component;

use A;
use F;
use File;
use Media;
use Obj;
use R;
use Redirect;

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
      'thumbs.memory'    => '128M',
      'thumbs.filename'  => false,
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

  }

  public function create($file, $params) {
    
    if(!$file->isWebsafe()) {
      return $file;
    }

    $self = $this;    

    // thumbnail destination builder
    \thumb::$defaults['destination'] = function($thumb) use($self, $file, $params) {

      $path = $self->path($file, $params);

      return new Obj([
        'root' => $self->kirby->roots()->thumbs() . DS . str_replace('/', DS, $path),
        'url'  => $self->kirby->urls()->thumbs()  . DS . $path,
      ]);
    
    };

    $thumb = new \Thumb($file, $params);
    $asset = new \Asset($thumb->result);

    return $thumb->exists() ? $asset : $file;

  }

  /**
   * Returns the clean path for a thumbnail
   * 
   * @param Media $file
   * @return string
   */
  protected function path(Media $file, $params) {
    return ltrim($this->dir($file) . '/' . $this->filename($file, $params), '/');
  }

  /**
   * 
   */
  protected function dir(Media $file) {
    if(is_a($file, 'File')) {
      return $file->page()->id();      
    } else {
      return str_replace($this->kirby->urls()->index(), '', dirname($file->url(true)));      
    }
  }

  /**
   * Returns the filename for a thumb including the 
   * identifying option hash
   * 
   * @param Media $file
   * @return string
   */
  protected function filename(Media $file, $params) {

    $dimensions = $this->dimensions($file, $params);

    $filename[] = $file->name();
    $filename[] = $this->dimensions($file, $params);
    $filename[] = $this->hash($params);

    return implode('-', array_filter($filename)) . '.' . $file->extension();

  }

  /**
   * Returns an identifying option hash for thumb filenames
   * 
   * @param array $params
   * @return string
   */
  protected function hash($params) {

    $args = $this->args($params);

    array_walk($args, function(&$value, $key) {
      if($value === true) {
        $value = $key;
      } else if($key === 'q' && $value == \thumb::$defaults['quality']) {
        $value = null;
      } else {
        $value = $key . $value;        
      }
    }); 

    return implode('-', array_filter($args));

  }

  protected function dimensions(Media $file, $params) {

    $dimensions = clone $file->dimensions();

    if(isset($params['crop']) && $params['crop']) {
      $dimensions->crop(a::get($params, 'width'), a::get($params, 'height'));
    } else {
      $dimensions->resize(a::get($params, 'width'), a::get($params, 'height'));
    }

    return $dimensions->width() . 'x' . $dimensions->height();

  }

  /**
   * Removes all unnecessary options and 
   * shortens the key in the array
   * 
   * @param array $params
   * @return string
   */
  protected function args($params) {

    $keys = [
      'blur'      => 'blur',
      'grayscale' => 'bw',
      'quality'   => 'q',
    ];

    $args = array_merge(\thumb::$defaults, $params);

    foreach($keys as $long => $short) {

      $value = a::get($args, $long);

      if(!empty($value)) {
        $query[$short] = $value;
      }

    }

    return $query;

  }

}