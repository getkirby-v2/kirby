<?php

/**
 *
 * File
 *
 * Low level file handling utilities
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class F {

  public static $mimes = array(
    'hqx'   => 'application/mac-binhex40',
    'cpt'   => 'application/mac-compactpro',
    'csv'   => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream'),
    'bin'   => 'application/macbinary',
    'dms'   => 'application/octet-stream',
    'lha'   => 'application/octet-stream',
    'lzh'   => 'application/octet-stream',
    'exe'   => array('application/octet-stream', 'application/x-msdownload'),
    'class' => 'application/octet-stream',
    'psd'   => 'application/x-photoshop',
    'so'    => 'application/octet-stream',
    'sea'   => 'application/octet-stream',
    'dll'   => 'application/octet-stream',
    'oda'   => 'application/oda',
    'pdf'   => array('application/pdf', 'application/x-download'),
    'ai'    => 'application/postscript',
    'eps'   => 'application/postscript',
    'ps'    => 'application/postscript',
    'smi'   => 'application/smil',
    'smil'  => 'application/smil',
    'mif'   => 'application/vnd.mif',
    'wbxml' => 'application/wbxml',
    'wmlc'  => 'application/wmlc',
    'dcr'   => 'application/x-director',
    'dir'   => 'application/x-director',
    'dxr'   => 'application/x-director',
    'dvi'   => 'application/x-dvi',
    'gtar'  => 'application/x-gtar',
    'gz'    => 'application/x-gzip',
    'php'   => array('text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'),
    'php3'  => array('text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'),
    'phtml' => array('text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'),
    'phps'  => array('text/php', 'text/x-php', 'application/x-httpd-php', 'application/php', 'application/x-php', 'application/x-httpd-php-source'),
    'js'    => 'application/x-javascript',
    'swf'   => 'application/x-shockwave-flash',
    'sit'   => 'application/x-stuffit',
    'tar'   => 'application/x-tar',
    'tgz'   => array('application/x-tar', 'application/x-gzip-compressed'),
    'xhtml' => 'application/xhtml+xml',
    'xht'   => 'application/xhtml+xml',
    'zip'   => array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
    'mid'   => 'audio/midi',
    'midi'  => 'audio/midi',
    'mpga'  => 'audio/mpeg',
    'mp2'   => 'audio/mpeg',
    'mp3'   => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
    'm4a'   => 'audio/mp4',    
    'aif'   => 'audio/x-aiff',
    'aiff'  => 'audio/x-aiff',
    'aifc'  => 'audio/x-aiff',
    'ram'   => 'audio/x-pn-realaudio',
    'rm'    => 'audio/x-pn-realaudio',
    'rpm'   => 'audio/x-pn-realaudio-plugin',
    'ra'    => 'audio/x-realaudio',
    'rv'    => 'video/vnd.rn-realvideo',
    'wav'   => 'audio/x-wav',
    'bmp'   => 'image/bmp',
    'gif'   => 'image/gif',
    'ico'   => 'image/x-icon',
    'jpg'   => array('image/jpeg', 'image/pjpeg'),
    'jpeg'  => array('image/jpeg', 'image/pjpeg'),
    'jpe'   => array('image/jpeg', 'image/pjpeg'),
    'png'   => 'image/png',
    'tiff'  => 'image/tiff',
    'tif'   => 'image/tiff',
    'svg'   => 'image/svg+xml',
    'css'   => 'text/css',
    'html'  => 'text/html',
    'htm'   => 'text/html',
    'shtml' => 'text/html',
    'txt'   => 'text/plain',
    'text'  => 'text/plain',
    'log'   => array('text/plain', 'text/x-log'),
    'rtx'   => 'text/richtext',
    'rtf'   => 'text/rtf',
    'xml'   => 'text/xml',
    'xsl'   => 'text/xml',
    'mpeg'  => 'video/mpeg',
    'mpg'   => 'video/mpeg',
    'mpe'   => 'video/mpeg',
    'mp4'   => 'video/mp4',
    'm4v'   => 'video/mp4',
    'qt'    => 'video/quicktime',
    'mov'   => 'video/quicktime',
    'avi'   => 'video/x-msvideo',
    'movie' => 'video/x-sgi-movie',
    'doc'   => 'application/msword',
    'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'dotx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
    'xls'   => array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
    'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'xltx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
    'ppt'   => array('application/powerpoint', 'application/vnd.ms-powerpoint'),
    'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'potx'  => 'application/vnd.openxmlformats-officedocument.presentationml.template',
    'word'  => array('application/msword', 'application/octet-stream'),
    'xl'    => 'application/excel',
    'eml'   => 'message/rfc822',
    'json'  => array('application/json', 'text/json'),
    'odt'   => 'application/vnd.oasis.opendocument.text',
    'odc'   => 'application/vnd.oasis.opendocument.chart',
    'odp'   => 'application/vnd.oasis.opendocument.presentation',
    'webm'  => 'video/webm'
  );

  public static $types = array(

    'image' => array(
      'jpeg',
      'jpg',
      'jpe',
      'gif',
      'png',
      'svg',
      'ico',
      'tif',
      'tiff',
      'bmp',
      'psd',
      'ai',
      'eps',
      'ps'
    ),

    'document' => array(
      'txt',
      'text',
      'mdown',
      'md',
      'markdown',
      'pdf',
      'doc',
      'docx',
      'dotx',
      'word',
      'xl',
      'xls',
      'xlsx',
      'xltx',
      'ppt',
      'pptx',
      'potx',
      'csv',
      'rtf',
      'rtx',
      'log',
      'odt',
      'odp',
      'odc',
    ),

    'archive' => array(
      'zip',
      'tar',
      'gz',
      'gzip',
      'tgz',
    ),

    'code' => array(
      'js',
      'css',
      'scss',
      'htm',
      'html',
      'shtml',
      'xhtml',
      'php',
      'php3',
      'php4',
      'rb',
      'xml',
      'json',
      'java',
      'py'
    ),

    'video' => array(
      'mov',
      'movie',
      'avi',
      'ogg',
      'ogv',
      'webm',
      'flv',
      'swf',
      'mp4',
      'm4v',
      'mpg',
      'mpe'
    ),

    'audio' => array(
      'mp3',
      'm4a',
      'wav',
      'aif',
      'aiff',
      'midi',
    ),

  );

  public static $units = array('B','kB','MB','GB','TB','PB', 'EB', 'ZB', 'YB');

  /**
   * Checks if a file exists
   *
   * @param string $file
   * @return boolean
   */
  public static function exists($file) {
    return file_exists($file);
  }

  /**
   * Safely requires a file if it exists
   */
  public static function load($file, $data = array()) {
    if(file_exists($file)) {
      extract($data);
      require($file);
    }
  }

  /**
   * Creates a new file
   *
   * <code>
   *
   * f::write('test.txt', 'hello');
   * // creates a new text file with hello as content
   *
   * // create a new file
   * f::write('text.txt', array('test' => 'hello'));
   * // creates a new file and encodes the array as json
   *
   * </code>
   *
   * @param  string  $file The path for the new file
   * @param  mixed   $content Either a string, an object or an array. Arrays and objects will be serialized.
   * @param  boolean $append true: append the content to an exisiting file if available. false: overwrite.
   * @return boolean
   */
  public static function write($file, $content, $append = false) {
    if(is_array($content) || is_object($content)) $content = serialize($content);
    $mode = ($append) ? FILE_APPEND | LOCK_EX : LOCK_EX;
    // if the parent directory does not exist, create it
    if(!is_dir(dirname($file))) {
      if(!dir::make(dirname($file))) return false;
    }
    return (@file_put_contents($file, $content, $mode) !== false) ? true : false;
  }

  /**
   * Appends new content to an existing file
   *
   * @param  string  $file The path for the file
   * @param  mixed   $content Either a string or an array. Arrays will be converted to JSON.
   * @return boolean
   */
  public static function append($file, $content) {
    return static::write($file,$content,true);
  }

  /**
   * Reads the content of a file
   *
   * <code>
   *
   * $content = f::read('test.txt');
   * // i.e. content is hello
   *
   * $content = f::read('text.txt', 'json');
   * // returns an array with the parsed content
   *
   * </code>
   *
   * @param  string $file The path for the file
   * @return mixed
   */
  public static function read($file) {
    return @file_get_contents($file);
  }

  /**
   * Returns the file content as base64 encoded string
   *
   * @param string $file The path for the file
   * @return string
   */
  public static function base64($file) {
    return base64_encode(f::read($file));
  }

  /**
   * Returns the file as data uri
   *
   * @param string $file The path for the file
   * @return string
   */
  public static function uri($file) {
    $mime = static::mime($file);
    return ($mime) ? 'data:' . $mime . ';base64,' . static::base64($file) : false;
  }

  /**
   * Moves a file to a new location
   *
   * <code>
   *
   * $move = f::move('test.txt', 'super.txt');
   *
   * if($move) echo 'The file has been moved';
   *
   * </code>
   *
   * @param  string $old The current path for the file
   * @param  string $new The path to the new location
   * @return boolean
   */
  public static function move($old, $new) {
    if(!file_exists($old) || file_exists($new)) return false;
    return @rename($old, $new);
  }

  /**
   * Copy a file to a new location.
   *
   * @param  string  $file
   * @param  string  $target
   * @return boolean
   */
  public static function copy($file, $target) {
    if(!file_exists($file) || file_exists($target)) return false;
    return @copy($file, $target);
  }

  /**
   * Deletes a file
   *
   * <code>
   *
   * $remove = f::remove('test.txt');
   * if($remove) echo 'The file has been removed';
   *
   * </code>
   *
   * @param  string  $file The path for the file
   * @return boolean
   */
  public static function remove($file) {
    return file_exists($file) && is_file($file) && !empty($file) ? @unlink($file) : false;
  }

  /**
   * Gets the extension of a file
   *
   * <code>
   *
   * $extension = f::extension('test.txt');
   * // extension is txt
   *
   * </code>
   *
   * @param  string  $file The filename or path
   * @param  string  $extension Set an optional extension to overwrite the current one
   * @return string
   */
  public static function extension($file, $extension = false) {

    // overwrite the current extension
    if($extension !== false) {
      return static::name($file) . '.' . $extension;
    }

    // return the current extension
    return strtolower(pathinfo($file, PATHINFO_EXTENSION));

  }

  /**
   * Returns all extensions for a certain file type
   *
   * @param string $type
   * @return array
   */
  public static function extensions($type = null) {
    if(is_null($type)) return array_keys(static::$mimes);
    return isset(static::$types[$type]) ? static::$types[$type] : array();
  }

  /**
   * Extracts the filename from a file path
   *
   * <code>
   *
   * $filename = f::filename('/var/www/test.txt');
   * // filename is test.txt
   *
   * </code>
   *
   * @param  string  $name The path
   * @return string
   */
  public static function filename($name) {
    return pathinfo($name, PATHINFO_BASENAME);
  }

  /**
   * Extracts the name from a file path or filename without extension
   *
   * <code>
   *
   * $name = f::name('/var/www/test.txt');
   *
   * // name is test
   *
   * </code>
   *
   * @param  string  $name The path or filename
   * @return string
   */
  public static function name($name) {
    return pathinfo($name, PATHINFO_FILENAME);
  }

  /**
   * Just an alternative for dirname() to stay consistent
   *
   * <code>
   *
   * $dirname = f::dirname('/var/www/test.txt');
   * // dirname is /var/www
   *
   * </code>
   *
   * @param  string  $file The path
   * @return string
   */
  public static function dirname($file) {
    return dirname($file);
  }

  /**
   * Returns the size of a file.
   *
   * <code>
   *
   * $size = f::size('/var/www/test.txt');
   * // size is ie: 1231939
   *
   * </code>
   *
   * @param  mixed  $file The path
   * @return mixed
   */
  public static function size($file) {
    return filesize($file);
  }

  /**
   * Converts an integer size into a human readable format
   *
   * <code>
   *
   * $niceSize = f::niceSize('/path/to/a/file.txt');
   * // nice size is i.e: 212 kb
   *
   * $niceSize = f::niceSize(1231939);
   * // nice size is: 1,2 mb
   *
   * </code>
   *
   * @param  mixed $size The file size or a file path
   * @return string
   */
  public static function niceSize($size) {

    // file mode
    if(is_string($size) && file_exists($size)) {
      $size = static::size($size);
    }

    // make sure it's an int
    $size = (int)$size;

    // avoid errors for invalid sizes
    if($size <= 0) return '0 kB';

    // the math magic
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . static::$units[$i];

  }

  /**
   * Get the file's last modification time.
   *
   * @param string $file
   * @param string $format
   * @param string $handler date or strftime
   * @return int
   */
  public static function modified($file, $format = null, $handler = 'date') {
    if(file_exists($file)) {
      return !is_null($format) ? $handler($format, filemtime($file)) : filemtime($file);      
    } else {
      return false;
    }
  }

  /**
   * Returns the mime type of a file
   *
   * @param string $file
   * @return mixed
   */
  public static function mime($file) {

    // stop for invalid files
    if(!file_exists($file)) return null;

    // Fileinfo is prefered if available
    if(function_exists('finfo_file')) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime  = finfo_file($finfo, $file);
      finfo_close($finfo);
      return $mime;
    }

    // for older versions with mime_content_type go for that.
    if(function_exists('mime_content_type') && $mime = @mime_content_type($file) !== false) {
      return $mime;
    }

    // shell check
    try {
      $mime = system::execute('file', [$file, '-z', '-b', '--mime'], 'output');  
      $mime = trim(str::split($mime, ';')[0]);
      if(f::mimeToExtension($mime)) return $mime;
    } catch(Exception $e) {
      // no mime type detectable with shell  
      $mime = null;
    }

    // Mime Sniffing
    $reader = new MimeReader($file);
    $mime   = $reader->get_type();

    if(!empty($mime) && f::mimeToExtension($mime)) {
      return $mime;
    }

    // guess the matching mime type by extension
    return f::extensionToMime(f::extension($file));

  }

  /**
   * Returns all detectable mime types
   *
   * @return array
   */
  public static function mimes() {
    return static::$mimes;
  }

  /**
   * Categorize the file
   *
   * @param string $file Either the file path or extension
   * @return string
   */
  public static function type($file) {

    $length = strlen($file);

    if($length >= 2 && $length <= 4) {
      // use the file name as extension
      $extension = $file;
    } else {
      // get the extension from the filename
      $extension = pathinfo($file, PATHINFO_EXTENSION);
    }

    if(empty($extension)) {
      // detect the mime type first to get the most reliable extension
      $mime      = static::mime($file);
      $extension = static::mimeToExtension($mime);
    }

    // sanitize extension
    $extension = strtolower($extension);

    foreach(static::$types as $type => $extensions) {
      if(in_array($extension, $extensions)) return $type;
    }

    return null;

  }

  /**
   * Returns an array of all available file types
   *
   * @return array
   */
  public static function types() {
    return static::$types;
  }

  /**
   * Checks if a file is of a certain type
   *
   * @param string $file Full path to the file
   * @param string $value An extension or mime type
   * @return boolean
   */
  public static function is($file, $value) {

    if(in_array($value, static::extensions())) {
      // check for the extension
      return static::extension($file) == $value;
    } else if(strpos($value, '/') !== false) {
      // check for the mime type
      return static::mime($file) == $value;
    }

    return false;

  }

  /**
   * Converts a mime type to a file extension
   *
   * @param string $mime
   * @return string
   */
  public static function mimeToExtension($mime) {
    foreach(static::$mimes as $key => $value) {
      if(is_array($value) && in_array($mime, $value)) return $key;
      if($value == $mime) return $key;
    }
    return null;
  }

  /**
   * Returns the type for a given mime
   *
   * @param string $mime
   * @return string
   */
  public static function mimeToType($mime) {
    return static::extensionToType(static::mimeToExtension($mime));
  }

  /**
   * Converts a file extension to a mime type
   *
   * @param string $extension
   * @return string
   */
  public static function extensionToMime($extension) {
    $mime = isset(static::$mimes[$extension]) ? static::$mimes[$extension] : null;
    return is_array($mime) ? array_shift($mime) : $mime;
  }

  /**
   * Returns the file type for a passed extension
   *
   * @param string $extension
   * @return string
   */
  public static function extensionToType($extension) {

    // get all categorized types
    foreach(static::$types as $type => $extensions) {
      if(in_array($extension, $extensions)) return $type;
    }

    return null;

  }

  /**
   * Sanitize a filename to strip unwanted special characters
   *
   * <code>
   *
   * $safe = f::safeName('über genious.txt');
   * // safe will be ueber-genious.txt
   *
   * </code>
   *
   * @param  string $string The file name
   * @return string
   */
  public static function safeName($string) {
    $name      = static::name($string);
    $extension = static::extension($string);
    $end       = !empty($extension) ? '.' . str::slug($extension) : '';
    return str::slug($name, '-', 'a-z0-9@._-') . $end;
  }

  /**
   * Checks if the file is writable
   *
   * @param string $file
   * @return boolean
   */
  public static function isWritable($file) {
    return is_writable($file);
  }

  /**
   * Checks if the file is readable
   *
   * @param string $file
   * @return boolean
   */
  public static function isReadable($file) {
    return is_readable($file);
  }

  /**
   * Read and send the file with the correct headers
   *
   * @param string $file
   */
  public static function show($file) {

    // stop the download if the file does not exist or is not readable
    if(!is_file($file) || !is_readable($file)) return false;

    // send the browser headers
    header::type(f::mime($file));

    // send the file
    die(f::read($file));

  }

  /*
   * Automatically sends all needed headers for the file to be downloaded
   * and echos the file's content
   *
   * @param string $file The root to the file
   * @param string $name Optional filename for the download
   */
  public static function download($file, $name = null) {

    // stop the download if the file does not exist or is not readable
    if(!is_file($file) || !is_readable($file)) return false;

    header::download(array(
      'name'     => $name ? $name : f::filename($file),
      'size'     => f::size($file),
      'mime'     => f::mime($file),
      'modified' => f::modified($file)
    ));

    die(f::read($file));

  }

  /**
   * Tries to find a file by various extensions
   * 
   * @param string $base
   * @param array $extensions
   * @return string|false
   */
  public static function resolve($base, $extensions) {
    foreach($extensions as $ext) {
      $file = $base . '.' . $ext;
      if(file_exists($file)) return $file;
    }
    return false;
  }

  /**
   * Unzips a zip file 
   * 
   * @param string $file
   * @param string $to
   * @return boolean
   */
  public static function unzip($file, $to) {

    if(!class_exists('ZipArchive')) {
      throw new Exception('The ZipArchive class is not available');
    }

    $zip = new ZipArchive;

    if($zip->open($file) === true) {
      $zip->extractTo($to);
      $zip->close();
      return true;
    } else {
      return false;
    }

  }

}
