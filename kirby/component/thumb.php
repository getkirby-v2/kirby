<?php

namespace Kirby\Component;

use A;
use Asset;
use F;
use File;
use Media;
use Obj;
use R;
use Redirect;
use Str;
use Thumb as Generator;
use Kirby\Component;

/**
 * Kirby Thumb Render and API Component
 *
 * @package   Kirby CMS
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://getkirby.com/license
 */
class Thumb extends Component {

  /**
   * Returns the default options for the thumb component
   * 
   * @return array
   */  
  public function defaults() {

    $self = $this;

    return [
      'thumbs.driver'      => 'gd',
      'thumbs.bin'         => 'convert',
      'thumbs.interlace'   => false,
      'thumbs.quality'     => 90,
      'thumbs.memory'      => '128M',
      'thumbs.filename'    => false,
      'thumbs.destination' => function($thumb) use($self) {

        $path = $self->path($thumb);

        return new Obj([
          'root' => $self->kirby->roots()->thumbs() . DS . str_replace('/', DS, $path),
          'url'  => $self->kirby->urls()->thumbs()  . '/' . $path,
        ]);

      }
    ];    
  }

  /**
   * Configures the thumb driver
   */
  public function configure() {

    $self = $this;    

    // setup the thumbnail location
    generator::$defaults['root'] = $this->kirby->roots->thumbs();
    generator::$defaults['url']  = $this->kirby->urls->thumbs();

    // setup the default thumbnail options
    generator::$defaults['driver']      = $this->kirby->option('thumbs.driver');
    generator::$defaults['bin']         = $this->kirby->option('thumbs.bin');
    generator::$defaults['quality']     = $this->kirby->option('thumbs.quality');
    generator::$defaults['interlace']   = $this->kirby->option('thumbs.interlace');
    generator::$defaults['memory']      = $this->kirby->option('thumbs.memory');
    generator::$defaults['destination'] = $this->kirby->option('thumbs.destination');
    generator::$defaults['filename']    = $this->kirby->option('thumbs.filename');

  }

  public function create($file, $params) {
    
    if(!$file->isWebsafe()) {
      return $file;
    }

    $thumb = new Generator($file, $params);
    $asset = new Asset($thumb->result);

    // store a reference to the original file
    $asset->original($file);

    return $thumb->exists() ? $asset : $file;

  }

  /**
   * Returns the clean path for a thumbnail
   * 
   * @param Generator $thumb
   * @return string
   */
  protected function path(Generator $thumb) {
    return ltrim($this->dir($thumb) . '/' . $this->filename($thumb), '/');
  }

  /**
   * @param Generator $thumb
   * @return string
   */
  protected function dir(Generator $thumb) {
    if(is_a($thumb->source, 'File')) {
      return $thumb->source->page()->id();      
    } else {
      return str_replace($this->kirby->urls()->index(), '', dirname($thumb->source->url()));      
    }
  }

  /**
   * Returns the filename for a thumb including the 
   * identifying option hash
   * 
   * @param Generator $thumb
   * @return string
   */
  protected function filename(Generator $thumb) {

    $dimensions = $this->dimensions($thumb);
    $wh         = $dimensions->width() . 'x' . $dimensions->height();
    $safeName   = f::safeName($thumb->source->name());
    $options    = $this->options($thumb);
    $extension  = $thumb->source->extension();

    if($thumb->options['filename'] === false) {
      return $safeName . '-' . $wh . r($options, '-' . $options) . '.' . $extension;
    } else {
      return str::template($thumb->options['filename'], [
        'extension'    => $extension,
        'name'         => $thumb->source->name(),
        'filename'     => $thumb->source->filename(),
        'safeName'     => $safeName,
        'safeFilename' => $safeName . '.' . $extension,
        'width'        => $dimensions->width(),
        'height'       => $dimensions->height(),
        'dimensions'   => $wh,
        'options'      => $options,
        'hash'         => md5($thumb->source->root() . $thumb->settingsIdentifier()),
      ]);
    }

  }

  /**
   * Returns an identifying option hash for thumb filenames
   * 
   * @param Generator $thumb
   * @return string
   */
  protected function options(Generator $thumb) {

    $keys = [
      'blur'      => 'blur',
      'grayscale' => 'bw',
      'quality'   => 'q',
    ];

    $string = [];

    foreach($keys as $long => $key) {

      $value = a::get($thumb->options, $long);

      if($key === 'blur') {

        if($value === false) {
          continue;
        }

        $value = a::get($thumb->options, 'blurpx');

        if($value == generator::$defaults['blurpx']) {
          $string[] = $key;
        } else {
          $string[] = $key . $value;
        }

      } else if($value === true) {
        $string[] = $key;
      } else if($value === false) {
        continue;
      } else if($key === 'q' && $value == generator::$defaults['quality']) {
        // ignore the default quality setting
        continue;
      } else {
        $string[] = $key . $value;        
      }

    }

    return implode('-', array_filter($string));

  }

  /**
   * @param Generator $thumb
   * @return string
   */
  protected function dimensions(Generator $thumb) {

    $dimensions = clone $thumb->source->dimensions();

    if(isset($thumb->options['crop']) && $thumb->options['crop']) {
      $dimensions->crop(a::get($thumb->options, 'width'), a::get($thumb->options, 'height'));
    } else {
      $dimensions->resize(a::get($thumb->options, 'width'), a::get($thumb->options, 'height'), a::get($thumb->options, 'upscale'));
    }

    return $dimensions;

  }

}
