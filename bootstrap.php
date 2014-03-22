<?php 

if(!defined('KIRBY')) define('KIRBY', true);
if(!defined('DS'))    define('DS', DIRECTORY_SEPARATOR);

// load the kirby toolkit 
include(__DIR__ . DS . 'toolkit' . DS . 'bootstrap.php');

// start a session
s::start();

// load all core classes
load(array(
  'childrenabstract'  => __DIR__ . DS . 'core' . DS . 'children.php',
  'contentabstract'   => __DIR__ . DS . 'core' . DS . 'content.php', 
  'fieldabstract'     => __DIR__ . DS . 'core' . DS . 'field.php', 
  'fileabstract'      => __DIR__ . DS . 'core' . DS . 'file.php', 
  'filesabstract'     => __DIR__ . DS . 'core' . DS . 'files.php', 
  'kirbytextabstract' => __DIR__ . DS . 'core' . DS . 'kirbytext.php', 
  'kirbytagabstract'  => __DIR__ . DS . 'core' . DS . 'kirbytag.php', 
  'pageabstract'      => __DIR__ . DS . 'core' . DS . 'page.php', 
  'siteabstract'      => __DIR__ . DS . 'core' . DS . 'site.php', 
  'blueprintabstract' => __DIR__ . DS . 'core' . DS . 'blueprint.php', 
  'usersabstract'     => __DIR__ . DS . 'core' . DS . 'users.php', 
  'userabstract'      => __DIR__ . DS . 'core' . DS . 'user.php', 
));

// load all helper functions
include(__DIR__ . DS . 'helpers.php');

// load the main kirby class
include(__DIR__ . DS . 'kirby.php');