<?php

if(!defined('KIRBY')) define('KIRBY', true);
if(!defined('DS'))    define('DS', DIRECTORY_SEPARATOR);

// load all dependencies
include(__DIR__ . DS . 'vendor' . DS . 'autoload.php');

// load all core classes
load(include(__DIR__ . DS . 'classmap.php'));

// load all helper functions
include(__DIR__ . DS . 'helpers.php');