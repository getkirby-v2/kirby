<?php 

// load the bootstrapper
include(__DIR__ . DIRECTORY_SEPARATOR . 'bootstrap.php');

// start the cms
echo kirby::start(array(
  'root.content' => $rootContent,
  'root.site'    => $rootSite
));