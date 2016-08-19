<?php

namespace Exif;

/**
 * Small class which hold info about the camera
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Camera {

  protected $make;
  protected $model;

  /**
   * Constructor
   *
   * @param array $exif
   */
  public function __construct($exif) {
    $this->make  = @$exif['Make'];
    $this->model = @$exif['Model'];
  }

  /**
   * Returns the make of the camera
   *
   * @return string
   */
  public function make() {
    return $this->make;
  }

  /**
   * Returns the camera model
   *
   * @return string
   */
  public function model() {
    return $this->model;
  }

  /**
   * Converts the object into a nicely readable array
   *
   * @return array
   */
  public function toArray() {
    return array(
      'make'  => $this->make,
      'model' => $this->model
    );
  }

  /**
   * Returns the full make + model name
   *
   * @return string
   */
  public function __toString() {
    return trim($this->make . ' ' . $this->model);
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return $this->toArray();
  }

}