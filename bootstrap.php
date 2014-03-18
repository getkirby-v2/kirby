<?php 

if(!defined('KIRBY')) define('KIRBY', true);
if(!defined('DS'))    define('DS', DIRECTORY_SEPARATOR);

// load the kirby toolkit 
include(__DIR__ . DS . 'toolkit' . DS . 'bootstrap.php');

// start a session
s::start();

// load all core classes
load(array(
  'childrenabstract' => __DIR__ . DS . 'core' . DS . 'children.php',
  'contentabstract'  => __DIR__ . DS . 'core' . DS . 'content.php', 
  'fieldabstract'    => __DIR__ . DS . 'core' . DS . 'field.php', 
  'fileabstract'     => __DIR__ . DS . 'core' . DS . 'file.php', 
  'filesabstract'    => __DIR__ . DS . 'core' . DS . 'files.php', 
  'ktabstract'       => __DIR__ . DS . 'core' . DS . 'kt.php', 
  'ktagabstract'     => __DIR__ . DS . 'core' . DS . 'ktag.php', 
  'pageabstract'     => __DIR__ . DS . 'core' . DS . 'page.php', 
  'siteabstract'     => __DIR__ . DS . 'core' . DS . 'site.php', 
));

// load all helper functions
include(__DIR__ . DS . 'helpers.php');

// load the main kirby class
include(__DIR__ . DS . 'kirby.php');