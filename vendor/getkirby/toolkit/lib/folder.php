<?php

/**
 * Folder
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Folder {

  // the root for the directory
  protected $root = null;

  // a cache for the scanned inventory
  protected $inventory = null;

  /**
   * Constructor
   */
  public function __construct($root) {
    if(file_exists($root) && is_file($root)) throw new Exception('Invalid folder: ' . $root);
    $this->root = $root;
  }

  /**
   * Returns the root of the directory
   */
  public function root() {
    return $this->root;
  }

  /**
   * Returns a md5 hash of the root 
   */
  public function hash() {
    return md5($this->root);
  }

  /**
   * Returns the name of the directory without the full path
   * 
   * @return string
   */
  public function name() {
    return basename($this->root);
  }

  /**
   * Returns the parent directory object
   * 
   * @return Directory
   */
  public function parent() {
    return new static(dirname($this->root));
  }

  /**
   * Checks if the dir exists
   * 
   * @return boolean
   */
  public function exists() {
    return is_dir($this->root);
  }

  /**
   * Creates the directory if it does not exist yet
   * 
   * @param boolean $recursive
   * @return boolean
   */
  public function make($recursive = true) {
    return dir::make($this->root, $recursive);
  }

  /**
   * Alternative for make
   * 
   * @param boolean $recursive
   * @return boolean
   */
  public function create($recursive = true) {
    return $this->make($recursive);
  }

  /**
   * Returns the entire content of the directory
   * 
   * @return array
   */
  public function inventory() {
    if(!is_dir($this->root)) return array();
    return $this->inventory = is_null($this->inventory) ? scandir($this->root) : $this->inventory;    
  }

  /**
   * Reads the directory content and returns an array with file objects
   * 
   * @param array $ignore
   * @return array
   */
  public function scan($ignore = null) {    
    $skip = is_array($ignore) ? $ignore : a::get(dir::$defaults, 'ignore', array());
    return empty($skip) ? $this->inventory() : (array)array_diff($this->inventory(), $skip);
  }

  /**
   * Alternative for scan
   * 
   * @param array $ignore
   * @return array
   */
  public function read($ignore = null) {
    return $this->scan($ignore);
  }

  /**
   * Returns a collection with full File and Directory objects
   * for each item in the directory
   * 
   * @param array $ignore
   * @return Collection
   */
  public function content($ignore = null) {
    $raw     = $this->scan($ignore);
    $root    = $this->root;
    $content = new Collection();

    foreach($raw as $file) {

      if(is_dir($root . DS . $file)) {
        $content->append($file, new static($root . DS . $file));
      } else {
        $content->append($file, new Media($root . DS . $file));
      }

    }

    return $content;

  }

  /**
   * Return a collection of all files within the directory
   * 
   * @param array $ignore
   * @param boolean $plain
   * @return mixed When $plain is true an array will be returned. Otherwise a Collection
   */
  public function files($ignore = null, $plain = false) {

    $raw = $this->scan($ignore);

    if($plain) {

      $content = array();

      foreach($raw as $file) {
        if(is_file($this->root . DS . $file)) $content[] = $file;
      }

    } else {

      $content = new Collection();

      foreach($raw as $file) {
        if(is_file($this->root . DS . $file)) {
          $content->append($file, new Media($this->root . DS . $file));
        }
      }

    }

    return $content;

  }

  /**
   * Return a collection of subfolders
   * 
   * @param array $ignore
   * @param boolean $plain
   * @return mixed If $plain is true an array will be returned. Otherwise a Collection
   */
  public function children($ignore = null, $plain = false) {

    $raw = $this->scan($ignore);

    if($plain) {

      $content = array();

      foreach($raw as $file) {
        if(is_dir($this->root . DS . $file)) $content[] = $file;
      }

    } else {

      $content = new Collection();

      foreach($raw as $file) {
        if(is_dir($this->root . DS . $file)) {
          $content->append($file, new static($this->root . DS . $file));
        }
      }

    }

    return $content;

  }

  /**
   * Returns a subfolder object by path
   * 
   * @return mixed Directory
   */
  public function child($path) {
    $root = $this->root . DS . str_replace('/', DS, $path);
    if(!is_dir($root)) return false;
    return new static($root);
  }

  /**
   * Corresponding method to File::type()
   * which makes it possible to filter a collection 
   * of files and directories by type. 
   * 
   * @return string
   */
  public function type() {
    return 'directory';
  }

  /**
   * Moves the directory to a new location
   * 
   * @param string $to
   * @return boolean
   */
  public function move($to) {
    if(!dir::move($this->root, $to)) {
      return false;
    } else {
      $this->root = true;
      return true;
    }
  }

  /**
   * Copies the directory to a new location
   * 
   * @param string $to
   * @return boolean
   */
  public function copy($to) {

    // Get content before destination directory is made
    // (prevents endless recursion)
    $content = $this->content();

    // Make destination directory
    $copy = new static($to);
    if(!$copy->make()) return false;

    // Loop through all subfiles and folders
    foreach($content as $item) {
      if(is_a($item, 'Folder')) {
        $dest = $to . DS . $item->name();
      } else {
        $dest = $to . DS . $item->filename();
      }
      if(!$item->copy($dest)) return false;
    }

    return $copy;

  }

  /**
   * Deletes the directory
   * 
   * @param boolean $keep Set this to true to keep the directory but delete all its content
   * @return boolean
   */
  public function delete($keep = false) {
    $items = $this->content(array('.', '..'));      
    foreach($items as $item) $item->delete();
    return $keep ? true : @rmdir($this->root);
  }

  /**
   * Alternative for delete
   * 
   * @param boolean $keep Set this to true to keep the directory but delete all its content
   * @return boolean
   */
  public function remove($keep = false) {
    return $this->delete($keep);
  }

  /**
   * Deletes all contents of the directory
   * 
   * @return boolean
   */
  public function flush() {
    return $this->delete(true);
  }

  /**
   * Alternative for flush
   * 
   * @return boolean
   */
  public function clean() {
    return $this->delete(true);
  }

  /**
   * Returns the entire size of the directory and all its contents
   * 
   * @return int
   */
  public function size() {

    $size  = 0;
    $items = $this->content(array('.', '..'));
    
    foreach($items AS $item) $size += $item->size();
    return $size;

  }

  /**
   * Returns the size as a human-readable string
   * 
   * @return string
   */
  public function niceSize() {
    return f::niceSize($this->size());
  }

  /**
   * Recursively check when the dir and all 
   * subfolders have been modified for the last time. 
   * 
   * @return  int  
   */  
  public function modified($format = null, $handler = 'date') {

    $modified = filemtime($this->root); 
    $items    = $this->scan(array('.', '..'));

    foreach($items AS $item) {      

      if(is_file($this->root . DS . $item)) {
        $newModified = filemtime($this->root . DS . $item);
      } else {      
        $object      = new static($this->root . DS . $item);
        $newModified = $object->modified();
      }
      
      $modified = ($newModified > $modified) ? $newModified : $modified;

    }
    
    return !is_null($format) ? $handler($format, $modified) : $modified;

  }

  /**
   * Checks if the directory is writable
   * 
   * @param boolean $recursive
   * @return boolean
   */
  public function isWritable($recursive = false) {
    if($recursive) {
      if(!$this->isWritable()) return false;
      foreach($this->content() as $f) {
        if(!$f->isWritable(true)) return false;
      }
      return true;
    }
    return is_writable($this->root);
  }

  /**
   * Checks if the directory is readable
   * 
   * @return boolean
   */
  public function isReadable() {
    return is_readable($this->root);
  }

  /**
   * Zip the current directory
   * 
   * @param string $to The path to the zip file
   */
  public function zip($to) {
    return dir::zip($this->root(), $to);
  }

  /**
   * Makes it possible to echo the entire object
   * 
   * @return string
   */
  public function __toString() {
    return $this->root;
  }

}
