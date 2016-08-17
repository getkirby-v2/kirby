<?php

/**
 * System
 *
 * Simplifies system (shell) calls
 *
 * @package   Kirby Toolkit
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Lukas Bestle
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class System {

  /**
   * Checks if the system() function is available
   *
   * @return boolean
   */
  public static function available() {
    return (!ini_get('safe_mode') && function_exists('exec'));
  }

  /**
   * Checks if a command is executable
   *
   * @param  string  $command Name or path of the command to check
   * @return boolean
   */
  public static function isExecutable($command) {
    // check if everything we need is available
    if(!static::available()) {
      throw new Exception('The exec() function is not available on this system. Probably, safe_mode is on (shame!).');
    }

    // only use the actual command
    list($command) = explode(' ', $command);

    // get the path to the executable and check if it exists
    $path = static::realpath($command);
    return $path !== false;
  }

  /**
   * Returns the path to a specific executable
   *
   * @param  string  $command Name or path of the command
   * @return mixed
   */
  public static function realpath($command) {
    // check if everything we need is available
    if(!static::available()) {
      throw new Exception('The exec() function is not available on this system. Probably, safe_mode is on (shame!).');
    }

    // if this is actually a file, we don't need to search for it any longer
    if(file_exists($command)) {
      return is_executable($command) ? realpath($command) : false;
    }

    // let the shell search for it
    // depends on the operating system
    if(strtolower(substr(PHP_OS, 0, 3)) === 'win') {
      // Windows
      // run the "where" command
      $result = `where $command`;
      // everything besides "Could not find files" would be OK
      $exists = !preg_match('/Could not find files/', $result);
    } else {
      // Unix
      // run the "which" command
      $result = `which $command`;
      // an empty output means there is no path
      $exists = !empty($result);
    }

    return $exists ? trim($result) : false;

  }

  /**
   * Execute a given shell command
   *
   * @param  string  $command Name or path of the command
   * @param  string  $arguments Additional arguments
   * @param  string  $what What to return ('status', 'success', 'output' or 'all')
   * @return mixed
   */
  public static function execute($command, $arguments = array(), $what = 'all') {
    // check if everything we need is available
    if(!static::available()) {
      throw new Exception('The exec() function is not available on this system. Probably, safe_mode is on (shame!).');
    }

    // other ways of calling this method
    if(is_array($command)) {
      // everything is given as one array
      $what = (is_array($arguments))? 'all' : $arguments;
      $arguments = array_slice($command, 1);
      $command = $command[0];
    } else if(!is_array($arguments)) {
      // each additional argument is given as a new method argument
      $arguments = array_slice(func_get_args(), 1);
      $what = 'all';
    }

    // check if the command exists
    if(!static::isExecutable($command)) {
      throw new Exception('The command "' . $command . '" is not executable.');
    }

    // escape command
    $command = escapeshellcmd($command);

    // escape arguments
    array_walk($arguments, function(&$argument) {
      $argument = escapeshellarg($argument);
    });

    // execute the command
    exec($command . ' ' . implode(' ', $arguments) . ' 2>&1', $output, $status);

    $result = array(
      'output'  => implode("\n", $output),
      'status'  => $status,
      'success' => $status === 0
    );

    // return an appropriate result
    if($what === 'all' || !array_key_exists($what, $result)) {
      return $result;
    } else {
      return $result[$what];
    }
  }

  /**
   * Execute a given shell command
   * Alias for System::execute()
   *
   * @param  string  $command Name or path of the command
   * @param  string  $arguments Additional arguments
   * @return array
   */
  public static function __callStatic($command, $arguments) {
    return static::execute($command, $arguments, 'all');
  }

}