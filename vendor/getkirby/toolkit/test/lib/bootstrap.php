<?php

$dir = realpath(dirname(__FILE__));

if(!defined('TEST_ROOT'))     define('TEST_ROOT',     dirname($dir));
if(!defined('TEST_ROOT_ETC')) define('TEST_ROOT_ETC', TEST_ROOT . DIRECTORY_SEPARATOR . 'etc');
if(!defined('TEST_ROOT_LIB')) define('TEST_ROOT_LIB', $dir);
if(!defined('TEST_ROOT_TMP')) define('TEST_ROOT_TMP', TEST_ROOT_ETC . DIRECTORY_SEPARATOR . 'tmp');

// set the timezone for all date functions
date_default_timezone_set('UTC');

// compatibility with both PHPUnit < 6 and >= 6
if(class_exists('\PHPUnit\Framework\TestCase') && !class_exists('\PHPUnit_Framework_TestCase')) {
  class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit_Framework_TestCase');
}

// include the kirby toolkit bootstrapper file
require_once(dirname(TEST_ROOT) . DIRECTORY_SEPARATOR . 'bootstrap.php');
