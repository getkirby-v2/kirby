<?php

/**
 * This is a legacy file, which makes it possible
 * to keep using the old v1 index.php with v2.
 * It's recommended to use the new index.php though.
 */

define('DS', DIRECTORY_SEPARATOR);

// load the bootstrapper
include(__DIR__ . DS . 'bootstrap.php');

// take the old variables to setup roots
$kirby = kirby();
$kirby->roots->index   = $root;
$kirby->roots->site    = $rootSite;
$kirby->roots->content = $rootContent;

// render
echo $kirby->launch();