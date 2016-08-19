<?php

if(!defined('KIRBY')) {
  
  define('KIRBY', true);
  
  // load all core classes
  load(include(__DIR__ . DS . 'classmap.php'));

  // load all helper functions
  include(__DIR__ . DS . 'helpers.php');

}