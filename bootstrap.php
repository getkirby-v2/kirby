<?php

if(!defined('KIRBY')) define('KIRBY', true);
if(!defined('DS'))    define('DS', DIRECTORY_SEPARATOR);

// load the kirby toolkit (path depends on installation method: Composer vs. submodule)
$path = (is_dir(dirname(__DIR__) . DS . 'toolkit'))? dirname(__DIR__) . DS . 'toolkit' : __DIR__ . DS . 'toolkit';
include($path . DS . 'bootstrap.php');

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
  'usersabstract'     => __DIR__ . DS . 'core' . DS . 'users.php',
  'userabstract'      => __DIR__ . DS . 'core' . DS . 'user.php',

  // vendors
  'parsedown'         => __DIR__ . DS . 'vendors' . DS . 'parsedown.php',
  'parsedownextra'    => __DIR__ . DS . 'vendors' . DS . 'parsedownextra.php'

));

// load all helper functions
include(__DIR__ . DS . 'helpers.php');

// load the main kirby class
include(__DIR__ . DS . 'kirby.php');