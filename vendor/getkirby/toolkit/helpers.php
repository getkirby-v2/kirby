<?php

/**
 * Shortcut for url::to()
 *
 * @return string
 */
function url() {
  return call_user_func_array('url::to', func_get_args());
}

/**
 * Even shorter shortcut for url::to()
 *
 * @return string
 */
function u() {
  return call_user_func_array('url::to', func_get_args());
}

/**
 * Redirects the user to a new URL
 * This uses the URL::to() method and can be super
 * smart with the custom url::to() handler. Check out
 * the URL class for more information
 */
function go() {
  call_user_func_array('redirect::to', func_get_args());
}

/**
 * Shortcut for r::get()
 *
 * @param   mixed    $key The key to look for. Pass false or null to return the entire request array.
 * @param   mixed    $default Optional default value, which should be returned if no element has been found
 * @return  mixed
 */
function get($key = null, $default = null) {
  return r::data($key, $default);
}

/**
 * Returns all params from the current url
 *
 * @return array
 */
function params() {
  return url::params();
}

/**
 * Get a parameter from the current URI object
 *
 * @param   mixed    $key The key to look for. Pass false or null to return the entire params array.
 * @param   mixed    $default Optional default value, which should be returned if no element has been found
 * @return  mixed
 */
function param($key = null, $default = null) {
  static $params;
  if(!$params) $params = url::params();
  return a::get($params, $key, $default);
}

/**
 * Smart version of return with an if condition as first argument
 *
 * @param mixed $condition
 * @param mixed $value The string to be returned if the condition is true
 * @param mixed $alternative An alternative string which should be returned when the condition is false
 * @return null
 */
function r($condition, $value, $alternative = null) {
  return $condition ? $value : $alternative;
}

/**
 * Smart version of echo with an if condition as first argument
 *
 * @param mixed $condition
 * @param mixed $value The string to be echoed if the condition is true
 * @param mixed $alternative An alternative string which should be echoed when the condition is false
 */
function e($condition, $value, $alternative = null) {
  echo r($condition, $value, $alternative);
}

/**
 * Alternative for e()
 *
 * @see e()
 * @param $condition
 * @param $value
 * @param null $alternative
 */
function ecco($condition, $value, $alternative = null) {
  e($condition, $value, $alternative);
}

/**
 * Dumps any array or object in a human readable way
 *
 * @param mixed $variable Whatever you like to inspect
 * @param boolean $echo
 * @return string
 */
function dump($variable, $echo = true) {
  if(r::cli()) {
    $output = print_r($variable, true) . PHP_EOL;
  } else {
    $output = '<pre>' . print_r($variable, true) . '</pre>';
  }
  if($echo === true) echo $output;
  return $output;
}

/**
 * Generates a single attribute or a list of attributes
 *
 * @see html::attr();
 * @param string $name mixed string: a single attribute with that name will be generated. array: a list of attributes will be generated. Don't pass a second argument in that case.
 * @param string $value if used for a single attribute, pass the content for the attribute here
 * @return string the generated html
 */
function attr($name, $value = null) {
  return html::attr($name, $value);
}

/**
 * Creates safe html by encoding special characters
 *
 * @param string $text unencoded text
 * @param bool $keepTags
 * @return string
 */
function html($text, $keepTags = true) {
  return html::encode($text, $keepTags);
}

/**
 * Shortcut for html()
 *
 * @see html()
 * @param $text
 * @param bool $keepTags
 * @return string
 */
function h($text, $keepTags = true) {
  return html::encode($text, $keepTags);
}

/**
 * Shortcut for xml::encode()
 *
 * @param $text
 * @return string
 */
function xml($text) {
  return xml::encode($text);
}

/**
 * Escape context specific output
 *
 * @param  string  $string  Untrusted data
 * @param  string  $context Location of output
 * @param  boolean $strict  Whether to escape an extended set of characters (HTML attributes only)
 * @return string  Escaped data
 */
function esc($string, $context = 'html', $strict = false) {
  if (method_exists('escape', $context)) {
    return escape::$context($string, $strict);
  }
}

/**
 * The widont function makes sure that there are no
 * typographical widows at the end of a paragraph –
 * that's a single word in the last line
 *
 * @param string $string
 * @return string
 */
function widont($string = '') {
  return str::widont($string);
}

/**
 * Convert a text to multiline text
 *
 * @param string $text
 * @return string
 */
function multiline($text) {
  return nl2br(html($text));
}

/**
 * Returns the memory usage in a readable format
 *
 * @return string
 */
function memory() {
  return f::niceSize(memory_get_usage());
}

/**
 * Determines the size/length of numbers, strings, arrays and countable objects
 *
 * @param mixed $value
 * @return int
 */
function size($value) {
  if(is_numeric($value)) return $value;
  if(is_string($value))  return str::length(trim($value));
  if(is_array($value))   return count($value);
  if(is_object($value)) {
    if($value instanceof Countable) return count($value);
  }
}

/**
 * Generates a gravatar image link
 *
 * @param string $email
 * @param int $size
 * @param string $default
 * @return string
 */
function gravatar($email, $size = 256, $default = 'mm') {
  return 'https://gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=' . urlencode($default) . '&s=' . $size;
}

/**
 * Checks / returns a csrf token
 *
 * @param string $check Pass a token here to compare it to the one in the session
 * @return mixed Either the token or a boolean check result
 */
function csrf($check = null) {

  // make sure a session is started
  s::start();

  // check explicitly if there have been no arguments at all
  // checking for null introduces a security issue!
  // see https://github.com/getkirby/getkirby.com/issues/340
  if(func_num_args() === 0) {
    // no arguments, generate/return a token

    $token = s::get('csrf');
    if(!$token) {
      $token = str::random(64);
      s::set('csrf', $token);
    }

    return $token;
  } else {
    // argument has been passed, check the token
    return $check === s::get('csrf');
  }

}

/**
 * Facepalm typo alias
 * @see csrf()
 */
function csfr() {
  return call('csrf', func_get_args());
}

/**
 * Shortcut for call_user_func_array with a better handling of arguments
 *
 * @param mixed $function
 * @param mixed $arguments
 * @return mixed
 */
function call($function, $arguments = array()) {
  if(!is_callable($function)) return false;
  if(!is_array($arguments)) $arguments = array($arguments);
  return call_user_func_array($function, $arguments);
}

/**
 * Parses yaml structured text
 *
 * @param $string
 * @return array
 */
function yaml($string) {
  return yaml::decode($string);
}

/**
 * Simple email sender helper
 *
 * @param array $params
 * @return Email
 */
function email($params = array()) {
  return new Email($params);
}

/**
 * Shortcut for the upload class
 *
 * @param $to
 * @param array $params
 * @return Upload
 */
function upload($to, $params = array()) {
  return new Upload($to, $params);
}

/**
 * Checks for invalid data
 *
 * @param array $data
 * @param array $rules
 * @param array $messages
 * @return array
 */
function invalid($data, $rules, $messages = []) {
  $errors = [];

  foreach ($rules as $field => $validations) {
    $validationIndex = -1;
    // See: http://php.net/manual/en/types.comparisons.php
    // only false for: null, undefined variable, '', []
    $filled = isset($data[$field]) && $data[$field] !== '' && $data[$field] !== [];

    $message = a::get($messages, $field, $field);
    // True if there is an error message for each validation method.
    $messageArray = is_array($message);

    foreach ($validations as $method => $options) {
      if (is_numeric($method)) {
        $method = $options;
      }
      $validationIndex++;

      if ($method === 'required') {
        if ($filled) {
          // Field is required and filled.
          continue;
        }
      } else if ($filled) {
        if (!is_array($options)) {
            $options = [$options];
        }
        array_unshift($options, a::get($data, $field));
        if (call(['v', $method], $options)) {
          // Field is filled and passes validation method.
          continue;
        }
      } else {
        // If a field is not required and not filled, no validation should be done.
        continue;
      }

      // If no continue was called we have a failed validation.
      if ($messageArray) {
        $errors[$field][] = a::get($message, $validationIndex, $field);
      } else {
        $errors[$field] = $message;
      }
    }
  }

  return $errors;
}


/**
 * Shortcut for the language variable getter
 *
 * @param string $key
 * @param mixed $default
 * @return string
 */
function l($key, $default = null) {
  return l::get($key, $default);
}

/**
 * @param $tag
 * @param bool $html
 * @param array $attr
 * @return Brick
 */
function brick($tag, $html = false, $attr = array()) {
  return new Brick($tag, $html, $attr);
}
