<?php

if(!defined('DS'))  define('DS', DIRECTORY_SEPARATOR);
if(!defined('MB'))  define('MB', (int)function_exists('mb_get_info'));
if(!defined('BOM')) define('BOM', "\xEF\xBB\xBF");

// polyfill for new sort flag
if(!defined('SORT_NATURAL')) define('SORT_NATURAL', 'SORT_NATURAL');

// a super simple autoloader
function load($classmap, $base = null) {
  spl_autoload_register(function($class) use ($classmap, $base) {
    $class = strtolower($class);
    if(!isset($classmap[$class])) return false;
    if($base) {
      include($base . DS . $classmap[$class]);      
    } else {
      include($classmap[$class]);
    }
  });
}

// auto-load all toolkit classes
load(array(

  // classes
  'a'                           => __DIR__ . DS . 'lib' . DS . 'a.php',
  'bitmask'                     => __DIR__ . DS . 'lib' . DS . 'bitmask.php',
  'brick'                       => __DIR__ . DS . 'lib' . DS . 'brick.php',
  'c'                           => __DIR__ . DS . 'lib' . DS . 'c.php',
  'cookie'                      => __DIR__ . DS . 'lib' . DS . 'cookie.php',
  'cache'                       => __DIR__ . DS . 'lib' . DS . 'cache.php',
  'cache\\driver'               => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'driver.php',
  'cache\\driver\\apc'          => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'driver' . DS . 'apc.php',
  'cache\\driver\\file'         => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'driver' . DS . 'file.php',
  'cache\\driver\\memcached'    => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'driver' . DS . 'memcached.php',
  'cache\\driver\\mock'         => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'driver' . DS . 'mock.php',
  'cache\\driver\\session'      => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'driver' . DS . 'session.php',
  'cache\\value'                => __DIR__ . DS . 'lib' . DS . 'cache' . DS . 'value.php',
  'collection'                  => __DIR__ . DS . 'lib' . DS . 'collection.php',
  'crypt'                       => __DIR__ . DS . 'lib' . DS . 'crypt.php',
  'data'                        => __DIR__ . DS . 'lib' . DS . 'data.php',
  'database'                    => __DIR__ . DS . 'lib' . DS . 'database.php',
  'database\\query'             => __DIR__ . DS . 'lib' . DS . 'database' . DS . 'query.php',
  'db'                          => __DIR__ . DS . 'lib' . DS . 'db.php',
  'detect'                      => __DIR__ . DS . 'lib' . DS . 'detect.php',
  'dimensions'                  => __DIR__ . DS . 'lib' . DS . 'dimensions.php',
  'dir'                         => __DIR__ . DS . 'lib' . DS . 'dir.php',
  'email'                       => __DIR__ . DS . 'lib' . DS . 'email.php',
  'embed'                       => __DIR__ . DS . 'lib' . DS . 'embed.php',
  'error'                       => __DIR__ . DS . 'lib' . DS . 'error.php',
  'errorreporting'              => __DIR__ . DS . 'lib' . DS . 'errorreporting.php',
  'escape'                      => __DIR__ . DS . 'lib' . DS . 'escape.php',
  'exif'                        => __DIR__ . DS . 'lib' . DS . 'exif.php',
  'exif\\camera'                => __DIR__ . DS . 'lib' . DS . 'exif' . DS . 'camera.php',
  'exif\\location'              => __DIR__ . DS . 'lib' . DS . 'exif' . DS . 'location.php',
  'f'                           => __DIR__ . DS . 'lib' . DS . 'f.php',
  'folder'                      => __DIR__ . DS . 'lib' . DS . 'folder.php',
  'header'                      => __DIR__ . DS . 'lib' . DS . 'header.php',
  'html'                        => __DIR__ . DS . 'lib' . DS . 'html.php',
  'i'                           => __DIR__ . DS . 'lib' . DS . 'i.php',
  'l'                           => __DIR__ . DS . 'lib' . DS . 'l.php',
  'media'                       => __DIR__ . DS . 'lib' . DS . 'media.php',
  'obj'                         => __DIR__ . DS . 'lib' . DS . 'obj.php',
  'pagination'                  => __DIR__ . DS . 'lib' . DS . 'pagination.php',
  'password'                    => __DIR__ . DS . 'lib' . DS . 'password.php',
  'r'                           => __DIR__ . DS . 'lib' . DS . 'r.php',
  'redirect'                    => __DIR__ . DS . 'lib' . DS . 'redirect.php',
  'remote'                      => __DIR__ . DS . 'lib' . DS . 'remote.php',
  'response'                    => __DIR__ . DS . 'lib' . DS . 'response.php',
  'router'                      => __DIR__ . DS . 'lib' . DS . 'router.php',
  's'                           => __DIR__ . DS . 'lib' . DS . 's.php',
  'server'                      => __DIR__ . DS . 'lib' . DS . 'server.php',
  'silo'                        => __DIR__ . DS . 'lib' . DS . 'silo.php',
  'sql'                         => __DIR__ . DS . 'lib' . DS . 'sql.php',
  'str'                         => __DIR__ . DS . 'lib' . DS . 'str.php',
  'system'                      => __DIR__ . DS . 'lib' . DS . 'system.php',
  'thumb'                       => __DIR__ . DS . 'lib' . DS . 'thumb.php',
  'timer'                       => __DIR__ . DS . 'lib' . DS . 'timer.php',
  'toolkit'                     => __DIR__ . DS . 'lib' . DS . 'toolkit.php',
  'tpl'                         => __DIR__ . DS . 'lib' . DS . 'tpl.php',
  'upload'                      => __DIR__ . DS . 'lib' . DS . 'upload.php',
  'url'                         => __DIR__ . DS . 'lib' . DS . 'url.php',
  'v'                           => __DIR__ . DS . 'lib' . DS . 'v.php',
  'visitor'                     => __DIR__ . DS . 'lib' . DS . 'visitor.php',
  'xml'                         => __DIR__ . DS . 'lib' . DS . 'xml.php',
  'yaml'                        => __DIR__ . DS . 'lib' . DS . 'yaml.php',

  // vendors
  'spyc'                        => __DIR__ . DS . 'vendors' . DS . 'yaml' . DS . 'yaml.php',
  'abeautifulsite\\simpleimage' => __DIR__ . DS . 'vendors' . DS . 'abeautifulsite' . DS . 'SimpleImage.php',
  'mimereader'                  => __DIR__ . DS . 'vendors' . DS . 'mimereader' . DS . 'mimereader.php',

));

// load all helpers
include(__DIR__ . DS . 'helpers.php');