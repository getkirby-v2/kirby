<?php

/**
 * Dir
 *
 * Low level directory handling utilities
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Dir {

  public static $defaults = array(
    'permissions' => 0755,
    'ignore'      => array('.', '..', '.DS_Store', '.gitignore', '.git', '.svn', '.htaccess', 'Thumb.db', '@eaDir')
  );

  /**
   * Creates a new directory
   *
   * <code>
   *
   * $create = dir::make('/app/test/new-directory');
   *
   * if($create) echo 'the directory has been created';
   *
   * </code>
   *
   * @param   string  $dir The path for the new directory
   * @return  boolean True: the dir has been created, false: creating failed
   */
  public static function make($dir, $recursive = true) {
    return is_dir($dir) ? true : @mkdir($dir, static::$defaults['permissions'], $recursive);
  }

  /**
   * Reads all files from a directory and returns them as an array.
   * It skips unwanted invisible stuff.
   *
   * <code>
   *
   * $files = dir::read('mydirectory');
   * // returns array('file-1.txt', 'file-2.txt', 'file-3.txt', etc...);
   *
   * </code>
   *
   * @param   string  $dir The path of directory
   * @param   array   $ignore Optional array with filenames, which should be ignored
   * @return  mixed   An array of filenames or false
   */
  public static function read($dir, $ignore = array()) {
    if(!is_dir($dir)) return array();
    $skip = array_merge(static::$defaults['ignore'], $ignore);
    return (array)array_diff(scandir($dir),$skip);
  }

  /**
   * Moves a directory to a new location
   *
   * <code>
   *
   * $move = dir::move('mydirectory', 'mynewdirectory');
   *
   * if($move) echo 'the directory has been moved to mynewdirectory';
   *
   * </code>
   *
   * @param   string  $old The current path of the directory
   * @param   string  $new The desired path where the dir should be moved to
   * @return  boolean True: the directory has been moved, false: moving failed
   */
  public static function move($old, $new) {
    if(!is_dir($old)) return false;
    return @rename($old, $new);
  }

  /**
   * Deletes a directory
   *
   * <code>
   *
   * $remove = dir::remove('mydirectory');
   *
   * if($remove) echo 'the directory has been removed';
   *
   * </code>
   *
   * @param   string   $dir The path of the directory
   * @param   boolean  $keep If set to true, the directory will flushed but not removed.
   * @return  boolean  True: the directory has been removed, false: removing failed
   */
  public static function remove($dir, $keep = false) {
    if(!is_dir($dir)) return false;

    // It's easier to handle this with the Folder class
    $object = new Folder($dir);
    return $object->remove($keep);
  }

  /**
   * Flushes a directory
   *
   * @param   string   $dir The path of the directory
   * @return  boolean  True: the directory has been flushed, false: flushing failed
   */
  public static function clean($dir) {
    return static::remove($dir, true);
  }

  /**
   * Gets the size of the directory and all subfolders and files
   *
   * @param   string $dir The path of the directory
   * @return  mixed
   */
  public static function size($dir) {

    if(!file_exists($dir)) return false;

    // It's easier to handle this with the Folder class
    $object = new Folder($dir);
    return $object->size();

  }

  /**
   * Returns a nicely formatted size of all the contents of the folder
   *
   * @param string $dir The path of the directory
   * @return mixed
   */
  public static function niceSize($dir) {
    return f::niceSize(static::size($dir));
  }

  /**
   * Recursively check when the dir and all
   * subfolders have been modified for the last time.
   *
   * @param   string   $dir The path of the directory
   * @param   string   $format
   * @return  int
   */
  public static function modified($dir, $format = null, $handler = 'date') {
    // It's easier to handle this with the Folder class
    $object = new Folder($dir);
    return $object->modified($format, $handler);
  }

  /**
   * Checks if the directory or any subdirectory has been
   * modified after the given timestamp
   *
   * @param string $dir
   * @param int $time
   * @return boolean
   */
  public static function wasModifiedAfter($dir, $time) {

    if(filemtime($dir) > $time) return true;

    $content = dir::read($dir);

    foreach($content as $item) {
      $subdir = $dir . DS . $item;
      if(filemtime($subdir) > $time) return true;
      if(is_dir($subdir) && dir::wasModifiedAfter($subdir, $time)) return true;
    }

    return false;

  }

  /**
   * Checks if the dir is writable
   *
   * @param string $dir
   * @return boolean
   */
  public static function writable($dir) {
    return is_writable($dir);
  }

  /**
   * Checks if the dir is readable
   *
   * @param string $dir
   * @return boolean
   */
  public static function readable($dir) {
    return is_readable($dir);
  }

  /**
   * Copy a file, or recursively copy a folder and its contents
   *
   * @param string $dir Source path
   * @param string $to Destination path
   */
  public static function copy($dir, $to) {
    // It's easier to handle this with the Folder class
    $object = new Folder($dir);
    return $object->copy($to);
  }

  /**
   * Zip a directory 
   * 
   * @param string $dir path to the directory
   * @param string $to path to the zip file
   * @return boolean
   */
  public static function zip($dir, $to) {

    if(!class_exists('ZipArchive')) {
      throw new Exception('The ZipArchive class is not available');
    }

    if(!is_dir($dir)) {
      return false;      
    }

    $zip = new ZipArchive;

    // remove any existing zip
    f::remove($to);

    if($zip->open($to, ZipArchive::CREATE) === false) {
      return false;
    }

    $dirs  = new RecursiveDirectoryIterator($dir);
    $files = new RecursiveIteratorIterator($dirs, RecursiveIteratorIterator::LEAVES_ONLY);

    foreach($files as $name => $file) {

      if($file->isDir()) continue;

      // Get real and relative path for current file
      $filePath     = $file->getRealPath();
      $relativePath = substr($filePath, strlen($dir) + 1);

      // Add current file to archive
      $zip->addFile($filePath, $relativePath);

    }

    $zip->close();

    return true;

  }

}