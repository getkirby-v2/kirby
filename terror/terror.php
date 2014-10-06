<?php

class Terror {

  static public $debug = false;

  static public function init() {

    static::$debug = ini_get('display_errors') ? true : false;

    // stop if there's already an error
    if($error = error_get_last()) {
      terror::error($error['message'], $error['type'], $error['file'], $error['line']);
    }

    /*
    // switch off conventional error reporting…
    error_reporting(0);

    // …to run our own
    ini_set('display_errors', 0);
    */

    // set a global error handler
    set_error_handler(array('Terror', 'errorHandler'));

    // track fatal errors
    register_shutdown_function(array('Terror', 'shutdownHandler'));

    // catch all uncaugth exceptions
    set_exception_handler(array('Terror', 'exceptionHandler'));

  }

  static public function debug() {
    return c::get('debug', static::$debug);
  }

  static public function errorHandler($code, $message, $file, $line) {
    if(error_reporting() !== 0) {
      //throw new ErrorException($message, 0, $code, $file, $line);
      terror::error($message, $code, $file, $line);
    }
  }

  static public function shutdownHandler() {
    if($error = error_get_last()) {
      terror::error($error['message'], $error['type'], $error['file'], $error['line']);
    }
  }

  static public function exceptionHandler($exception) {
    terror::error($exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine());
  }

  static public function error($message, $type, $file, $line) {

    // remove everything that has been rendered so far
    if(ob_get_level()) ob_end_clean();

    if(class_exists('kirby') and !is_null(kirby::$instance)) {
      $kirby = kirby::$instance;
    } else {
      $kirby = null;
    }

    if(r::ajax()) {
      if(terror::debug()) {
        echo response::error($message, 400, array(
          'type' => $type,
          'file' => $file,
          'line' => $line
        ));
      } else {
        echo response::error('Unexpected error', 400);
      }
    } else {
      header::status(400);
      static::view($message, $type, $file, $line, $kirby);
    }

    die();
  }


  static public function view($message, $type, $file, $line, $kirby) {
    if(terror::debug()) {
      $extract = terror::extract($file, $line);
      require(__DIR__ . DS . 'views' . DS . 'debug.php');
    } else {
      require(__DIR__ . DS . 'views' . DS . 'sorry.php');
    }
  }

  static public function extract($file, $line) {

    $content = f::read($file);
    $lines   = preg_split('/\r\n|\n|\r/', $content);

    $begin = $line - 5;

    if($begin < 0) $begin = 0;

    $end   = 10;
    $lines = array_slice($lines, $begin, $end);
    $html  = '';
    $n     = $begin+1;

    foreach($lines as $l) {

      if(empty($l)) $l = ' ';

      $num = '<span class="code-line-number">' . $n . '</span>';

      if($n == $line) {
        $html .= '<span class="code-line code-line-highlighted">' . $num . htmlspecialchars($l) . '</span>';
      } else {
        $html .= '<span class="code-line">' . $num . htmlspecialchars($l) . '</span>';
      }
      $n++;
    }

    return $html;

  }

}