<?php

namespace Kirby;

use Kirby;
use R;
use Response;
use Toolkit;
use Tpl;
use Visitor;

use Whoops\Run;
use Whoops\Handler\Handler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\CallbackHandler;

class ErrorHandling {

  public $kirby;
  public $whoops;

  public function __construct(Kirby $kirby) {

    if($kirby->options['whoops'] === false) {

      if($kirby->options['debug'] === true) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
      } else if($kirby->options['debug'] === false) {
        error_reporting(0);
        ini_set('display_errors', 0);
      }

      return;

    }

    $this->kirby  = $kirby;
    $this->whoops = new Run;

    if(r::ajax() || visitor::acceptance('application/json') > visitor::acceptance('text/html')) {
      $this->json();
    } else if(r::cli()) {
      $this->cli();
    } else {
      $this->html();
    }

    $this->whoops->register();

  }

  public function json() {

    $kirby   = $this->kirby;   
    $handler = new CallbackHandler(function($exception, $inspector, $run) use($kirby) {

      if($kirby->options['debug'] === true) {
        echo response::json([
          'status'  => 'error',
          'code'    => $exception->getCode(),
          'message' => $exception->getMessage() . ' in file: ' . $exception->getFile() . ' on line: ' . $exception->getLine(),
        ], 500);
      } else {
        echo response::json([
          'status'  => 'error',
          'code'    => 0,
          'message' => 'An unexpected error occurred! Enable debug mode for more info: https://getkirby.com/docs/cheatsheet/options/debug',
        ], 500);        
      }

      return Handler::QUIT;

    });
  
    $this->whoops->pushHandler($handler);      

  }

  public function cli() {

    $handler = new PlainTextHandler;

    $this->whoops->pushHandler($handler);      

  }

  public function html() {

    if($this->kirby->options['debug'] === true) {

      $handler = new PrettyPageHandler;
      $handler->setPageTitle('Kirby CMS Debugger');
      $handler->addDataTableCallback('Kirby', function() {
        return [
          'Kirby Toolkit' => 'v' . toolkit::$version,
          'Kirby CMS'     => 'v' . kirby::$version,
        ];
      });

    } else {

      $handler = new CallbackHandler(function($exception, $inspector, $run) {
        $html = tpl::load(dirname(__DIR__) . DS . 'views' . DS . 'fatal.php');
        echo new Response($html, 'html', 500);
        return Handler::QUIT;
      });

    }

    $this->whoops->pushHandler($handler);      

  }

}