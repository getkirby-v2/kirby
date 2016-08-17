<?php

/**
 * Array
 *
 * This class is supposed to simplify array handling
 * and make it more consistent.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class A {

  /**
   * Gets an element of an array by key
   *
   * <code>
   *
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );
   *
   * echo a::get($array, 'cat');
   * // output: 'miao'
   *
   * echo a::get($array, 'elephant', 'shut up');
   * // output: 'shut up'
   *
   * $catAndDog = a::get(array('cat', 'dog'));
   * // result: array(
   * //   'cat' => 'miao',
   * //   'dog' => 'wuff'
   * // );
   *
   * </code>
   *
   * @param   array  $array The source array
   * @param   mixed  $key The key to look for
   * @param   mixed  $default Optional default value, which should be returned if no element has been found
   * @return  mixed
   */
  public static function get($array, $key, $default = null) {

    // get an array of keys
    if(is_array($key)) {
      $result = array();
      foreach($key as $k) $result[$k] = static::get($array, $k);
      return $result;

    // get a single
    } else if(isset($array[$key])) {
      return $array[$key];

    // return the entire array if the key is null
    } else if(is_null($key)) {
      return $array;

    // get the default value if nothing else worked out
    } else {
      return $default;
    }

  }

  /**
   * Shows an entire array or object in a human readable way
   * This is perfect for debugging
   *
   * <code>
   *
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );
   *
   * a::show($array);
   *
   * // output:
   * // Array
   * // (
   * //     [cat] => miao
   * //     [dog] => wuff
   * //     [bird] => tweet
   * // )
   *
   * </code>
   *
   * @param   array    $array The source array
   * @param   boolean  $echo By default the result will be echoed instantly. You can switch that off here.
   * @return  mixed    If echo is false, this will return the generated array output.
   */
  public static function show($array, $echo = true) {
    return dump($array, $echo);
  }

  /**
   * Converts an array to a JSON string
   * It's basically a shortcut for json_encode()
   *
   * <code>
   *
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );
   *
   * echo a::json($array);
   * // output: {"cat":"miao","dog":"wuff","bird":"tweet"}
   *
   * </code>
   *
   * @param   array   $array The source array
   * @return  string  The JSON string
   */
  public static function json($array) {
    return json_encode((array)$array);
  }

  /**
   * Converts an array to a XML string
   *
   * <code>
   *
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );
   *
   * echo a::xml($array, 'animals');
   * // output:
   * // <animals>
   * //   <cat>miao</cat>
   * //   <dog>wuff</dog>
   * //   <bird>tweet</bird>
   * // </animals>
   *
   * </code>
   *
   * @param   array    $array The source array
   * @param   string   $tag The name of the root element
   * @param   boolean  $head Include the xml declaration head or not
   * @param   string   $charset The charset, which should be used for the header
   * @param   int      $level The indendation level
   * @return  string   The XML string
   */
  public static function xml($array, $tag = 'root', $head = true, $charset = 'utf-8', $tab = '  ', $level = 0) {
    return xml::create($array, $tag, $head, $charset, $tab, $level);
  }

  /**
   * Extracts a single column from an array
   *
   * <code>
   *
   * $array[0] = array(
   *   'id' => 1,
   *   'username' => 'bastian',
   * );
   *
   * $array[1] = array(
   *   'id' => 2,
   *   'username' => 'peter',
   * );
   *
   * $array[3] = array(
   *   'id' => 3,
   *   'username' => 'john',
   * );
   *
   * $extract = a::extract($array, 'username');
   *
   * // result: array(
   * //   'bastian',
   * //   'peter',
   * //   'john'
   * // );
   *
   * </code>
   *
   * @param   array   $array The source array
   * @param   string  $key The key name of the column to extract
   * @return  array   The result array with all values from that column.
   */
  public static function extract($array, $key) {
    $output = array();
    foreach($array AS $a) if(isset($a[$key])) $output[] = $a[ $key ];
    return $output;
  }

  /**
   * Shuffles an array and keeps the keys
   *
   * <code>
   *
   * $array = array(
   *   'cat'  => 'miao',
   *   'dog'  => 'wuff',
   *   'bird' => 'tweet'
   * );
   *
   * $shuffled = a::shuffle($array);
   * // output: array(
   * //    'dog' => 'wuff',
   * //    'cat' => 'miao',
   * //    'bird' => 'tweet'
   * // );
   *
   * </code>
   *
   * @param   array  $array The source array
   * @return  array  The shuffled result array
   */
  public static function shuffle($array) {

    $keys = array_keys($array);
    $new  = array();

    shuffle($keys);

    // resort the array
    foreach($keys as $key) $new[$key] = $array[$key];
    return $new;

  }

  /**
   * Returns the first element of an array
   *
   * I always have to lookup the names of that function
   * so I decided to make this shortcut which is
   * easier to remember.
   *
   * <code>
   *
   * $array = array(
   *   'cat',
   *   'dog',
   *   'bird',
   * );
   *
   * $first = a::first($array);
   * // first: 'cat'
   *
   * </code>
   *
   * @param   array  $array The source array
   * @return  mixed  The first element
   */
  public static function first($array) {
    return array_shift($array);
  }

  /**
   * Returns the last element of an array
   *
   * I always have to lookup the names of that function
   * so I decided to make this shortcut which is
   * easier to remember.
   *
   * <code>
   *
   * $array = array(
   *   'cat',
   *   'dog',
   *   'bird',
   * );
   *
   * $last = a::last($array);
   * // first: 'bird'
   *
   * </code>
   *
   * @param   array  $array The source array
   * @return  mixed  The last element
   */
  public static function last($array) {
    return array_pop($array);
  }

  /**
   * Fills an array up with additional elements to certain amount.
   *
   * <code>
   *
   * $array = array(
   *   'cat',
   *   'dog',
   *   'bird',
   * );
   *
   * $result = a::fill($array, 5, 'elephant');
   *
   * // result: array(
   * //   'cat',
   * //   'dog',
   * //   'bird',
   * //   'elephant',
   * //   'elephant',
   * // );
   *
   * </code>
   *
   * @param   array  $array The source array
   * @param   int    $limit The number of elements the array should contain after filling it up.
   * @param   mixed  $fill The element, which should be used to fill the array
   * @return  array  The filled-up result array
   */
  public static function fill($array, $limit, $fill='placeholder') {
    if(count($array) < $limit) {
      $diff = $limit-count($array);
      for($x=0; $x<$diff; $x++) $array[] = $fill;
    }
    return $array;
  }

  /**
   * Checks for missing elements in an array
   *
   * This is very handy to check for missing
   * user values in a request for example.
   *
   * <code>
   *
   * $array = array(
   *   'cat' => 'miao',
   *   'dog' => 'wuff',
   *   'bird' => 'tweet'
   * );
   *
   * $required = array('cat', 'elephant');
   *
   * $missng = a::missing($array, $required);
   * // missing: array(
   * //    'elephant'
   * // );
   *
   * </code>
   *
   * @param   array  $array The source array
   * @param   array  $required An array of required keys
   * @return  array  An array of missing fields. If this is empty, nothing is missing.
   */
  public static function missing($array, $required=array()) {
    $missing = array();
    foreach($required AS $r) {
      if(empty($array[$r])) $missing[] = $r;
    }
    return $missing;
  }

  /**
   * Sorts a multi-dimensional array by a certain column
   *
   * <code>
   *
   * $array[0] = array(
   *   'id' => 1,
   *   'username' => 'bastian',
   * );
   *
   * $array[1] = array(
   *   'id' => 2,
   *   'username' => 'peter',
   * );
   *
   * $array[3] = array(
   *   'id' => 3,
   *   'username' => 'john',
   * );
   *
   * $sorted = a::sort($array, 'username ASC');
   * // Array
   * // (
   * //      [0] => Array
   * //          (
   * //              [id] => 1
   * //              [username] => bastian
   * //          )
   * //      [1] => Array
   * //          (
   * //              [id] => 3
   * //              [username] => john
   * //          )
   * //      [2] => Array
   * //          (
   * //              [id] => 2
   * //              [username] => peter
   * //          )
   * // )
   *
   * </code>
   *
   * @param   array   $array The source array
   * @param   string  $field The name of the column
   * @param   string  $direction desc (descending) or asc (ascending)
   * @param   const   $method A PHP sort method flag or 'natural' for natural sorting, which is not supported in PHP by sort flags
   * @return  array   The sorted array
   */
  public static function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR) {

    $direction = strtolower($direction) == 'desc' ? SORT_DESC : SORT_ASC;
    $helper    = array();
    $result    = array();

    // build the helper array
    foreach($array as $key => $row) $helper[$key] = $row[$field];

    // natural sorting
    if($method === SORT_NATURAL) {
      natsort($helper);
      if($direction === SORT_DESC) $helper = array_reverse($helper);
    } else if($direction === SORT_DESC) {
      arsort($helper, $method);
    } else {
      asort($helper, $method);
    }

    // rebuild the original array
    foreach($helper as $key => $val) $result[$key] = $array[$key];

    return $result;

  }

  /**
   * Checks wether an array is associative or not (experimental)
   *
   * @param   array    $array The array to analyze
   * @return  boolean  true: The array is associative false: It's not
   */
  public static function isAssociative($array) {
    return !ctype_digit(implode(NULL, array_keys($array)));
  }

  /**
   * Returns the average value of an array
   *
   * @param   array  $array The source array
   * @param   int    $decimals The number of decimals to return
   * @return  int    The average value
   */
  public static function average($array, $decimals = 0) {
    return round(array_sum($array), $decimals) / sizeof($array);
  }

  /**
   * Merges arrays recursively
   *
   * @param array $array1
   * @param array $array2
   * @return array
   */
  public static function merge($array1, $array2) {
    $merged = $array1;
    foreach($array2 as $key => $value) {
      if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
        $merged[$key] = static::merge($merged[$key], $value);
      } else {
        $merged[$key] = $value;
      }
    }
    return $merged;
  }

  /**
   * Update an array with a second array
   * The second array can contain callbacks as values, 
   * which will get the original values as argument
   * 
   * @param array $array
   * @param array $update
   */
  public static function update($array, $update) {

    foreach($update as $key => $value) {
      if(is_a($value, 'Closure')) {
        $array[$key] = call($value, static::get($array, $key));
      } else {
        $array[$key] = $value;
      }
    }

    return $array;

  }

}