<?php

namespace Kirby\Component;

use A;
use F;
use File;
use R;
use Redirect;
use Obj;

class Thumb extends \Kirby\Component {

  public function api($page, $file, $options) {

    $self = $this;

    \thumb::$defaults['destination'] = function($thumb) use($self, $page, $file) {

      $path = $self->path($file, $thumb->options);

      return new Obj([
        'root' => $self->kirby->roots()->thumbs() . DS . str_replace('/', DS, $path),
        'url'  => $self->kirby->urls()->thumbs() . '/' . $path,
      ]);

    };

    $thumb = new \Thumb($file, $this->options($options));

    // redirect to the generated thumbnail
    redirect::send($thumb->url(), 307);

  }

  /**
   * Translate the request query into options for 
   * the thumbnail class
   * 
   * @param array $options
   * @return array
   */
  protected function options($options) {
    return [
      'width'      => $this->getIntegerOption($options, 'w', null), 
      'height'     => $this->getIntegerOption($options, 'h', null),
      'quality'    => $this->getIntegerOption($options, 'q', 90),
      'grayscale'  => $this->getBooleanOption($options, 'bw'),
      'crop'       => $this->getBooleanOption($options, 'crop'),
      'blur'       => $this->getBooleanOption($options, 'blur'),
      'upscale'    => $this->getBooleanOption($options, 'upscale'),
      'autoOrient' => $this->getBooleanOption($options, 'autoOrient'),
      'interlace'  => $this->getBooleanOption($options, 'interlace'),
      'overwrite'  => true
    ];
  }

  protected function getBooleanOption($options, $key, $default = null) {
    $value = a::get($options, $key, $default);
    return ($value === true || $value === 1 || $value === 'true' || $value === '1');
  }

  protected function getIntegerOption($options, $key, $default = null) {
    $value = a::get($options, $key, $default);
    return $value === null ? null : intval($value);    
  }

  protected function query($options) {

    $keys = [
      'width'     => 'w', 
      'height'    => 'h', 
      'quality'   => 'q',
      'crop'      => 'crop',
      'blur'      => 'blur',
      'grayscale' => 'bw'
    ];

    $options = array_merge(\thumb::$defaults, $options);
    $query   = [];

    foreach($keys as $long => $short) {

      $value = a::get($options, $long);

      if(!empty($value)) {
        $query[$short] = $value;
      }

    }

    return http_build_query($query);

  }


  public function path($file, $options = []) {

    $page  = $file->page();
    $query = is_array($options) ? $this->query($options) : $options;
    $name  = str_replace('@', '-', f::safeName($file->name()));
    $path  = $page->id() . '/' . $name . r(!empty($query), '@') . $query . '.' . $file->extension();
    $path  = ltrim($path, '/');

    return $path;

  }

  public function url(File $file) {

    // build the thumb query
    $query    = $this->query($file->thumb);
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

}