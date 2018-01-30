<?php

/**
 *
 * String
 *
 * A set of handy string methods
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Str {

  public static $ascii = array(
    '/Ä/' => 'Ae',
    '/æ|ǽ|ä/' => 'ae',
    '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ|А/' => 'A',
    '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª|а/' => 'a',
    '/Б/' => 'B',
    '/б/' => 'b',
    '/Ç|Ć|Ĉ|Ċ|Č|Ц/' => 'C',
    '/ç|ć|ĉ|ċ|č|ц/' => 'c',
    '/Ð|Ď|Đ/' => 'Dj',
    '/ð|ď|đ/' => 'dj',
    '/Д/' => 'D',
    '/д/' => 'd',
    '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě|Е|Ё|Э/' => 'E',
    '/è|é|ê|ë|ē|ĕ|ė|ę|ě|е|ё|э/' => 'e',
    '/Ф/' => 'F',
    '/ƒ|ф/' => 'f',
    '/Ĝ|Ğ|Ġ|Ģ|Г/' => 'G',
    '/ĝ|ğ|ġ|ģ|г/' => 'g',
    '/Ĥ|Ħ|Х/' => 'H',
    '/ĥ|ħ|х/' => 'h',
    '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ|И/' => 'I',
    '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı|и/' => 'i',
    '/Ĵ|Й/' => 'J',
    '/ĵ|й/' => 'j',
    '/Ķ|К/' => 'K',
    '/ķ|к/' => 'k',
    '/Ĺ|Ļ|Ľ|Ŀ|Ł|Л/' => 'L',
    '/ĺ|ļ|ľ|ŀ|ł|л/' => 'l',
    '/М/' => 'M',
    '/м/' => 'm',
    '/Ñ|Ń|Ņ|Ň|Н/' => 'N',
    '/ñ|ń|ņ|ň|ŉ|н/' => 'n',
    '/Ö/' => 'Oe',
    '/œ|ö/' => 'oe',
    '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ|О/' => 'O',
    '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º|о/' => 'o',
    '/П/' => 'P',
    '/п/' => 'p',
    '/Ŕ|Ŗ|Ř|Р/' => 'R',
    '/ŕ|ŗ|ř|р/' => 'r',
    '/Ś|Ŝ|Ş|Ș|Š|С/' => 'S',
    '/ś|ŝ|ş|ș|š|ſ|с/' => 's',
    '/Ţ|Ț|Ť|Ŧ|Т/' => 'T',
    '/ţ|ț|ť|ŧ|т/' => 't',
    '/Ü/' => 'Ue',
    '/ü/' => 'ue',
    '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ|У/' => 'U',
    '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ|у/' => 'u',
    '/В/' => 'V',
    '/в/' => 'v',
    '/Ý|Ÿ|Ŷ|Ы/' => 'Y',
    '/ý|ÿ|ŷ|ы/' => 'y',
    '/Ŵ/' => 'W',
    '/ŵ/' => 'w',
    '/Ź|Ż|Ž|З/' => 'Z',
    '/ź|ż|ž|з/' => 'z',
    '/Æ|Ǽ/' => 'AE',
    '/ß/'=> 'ss',
    '/Ĳ/' => 'IJ',
    '/ĳ/' => 'ij',
    '/Œ/' => 'OE',
    '/Ч/' => 'Ch',
    '/ч/' => 'ch',
    '/Ю/' => 'Ju',
    '/ю/' => 'ju',
    '/Я/' => 'Ja',
    '/я/' => 'ja',
    '/Ш/' => 'Sh',
    '/ш/' => 'sh',
    '/Щ/' => 'Shch',
    '/щ/' => 'shch',
    '/Ж/' => 'Zh',
    '/ж/' => 'zh',
  );

  /**
   * Default options for string methods
   *
   * @var array
   */
  public static $defaults = array(
    'slug' => array(
      'separator' => '-',
      'allowed'   => 'a-z0-9'
    )
  );

  /**
   * Converts a string to a html-safe string
   *
   * <code>
   *
   * echo str::html('some <em>über crazy</em> stuff');
   * // output: some <em>&uuml;ber crazy</em> stuff
   *
   * echo str::html('some <em>über crazy</em> stuff', false);
   * // output: some &lt;em&gt;&uuml;ber crazy&lt;/em&gt; stuff
   *
   * </code>
   *
   * @param  string  $string
   * @param  boolean $keepTags True: lets stuff inside html tags untouched.
   * @return string  The html string
   */
  public static function html($string, $keepTags = true) {
    return html::encode($string, $keepTags);
  }

  /**
   * Removes all html tags and encoded chars from a string
   *
   * <code>
   *
   * echo str::unhtml('some <em>crazy</em> stuff');
   * // output: some uber crazy stuff
   *
   * </code>
   *
   * @param  string  $string
   * @return string  The html string
   */
  public static function unhtml($string) {
    return html::decode($string);
  }

  /**
   * Converts a string to a xml-safe string
   * Converts it to html-safe first and then it
   * will replace html entities to xml entities
   *
   * <code>
   *
   * echo str::xml('some über crazy stuff');
   * // output: some &#252;ber crazy stuff
   *
   * </code>
   *
   * @param  string  $text
   * @param  boolean $html True: convert to html first
   * @return string
   */
  public static function xml($text, $html = true) {
    return xml::encode($text, $html);
  }

  /**
   * Removes all xml entities from a string
   * and convert them to html entities first
   * and remove all html entities afterwards.
   *
   * <code>
   *
   * echo str::unxml('some <em>&#252;ber</em> crazy stuff');
   * // output: some &uuml;ber crazy stuff
   *
   * </code>
   *
   * @param  string  $string
   * @return string
   */
  public static function unxml($string) {
    return xml::decode($string);
  }

  /**
   * Parses a string by a set of available methods
   *
   * Available methods:
   * - json
   * - xml
   * - url
   * - query
   * - php
   *
   * <code>
   *
   * str::parse('{"test":"cool","super":"genious"}');
   * // output: array(
   * //  'test' => 'cool',
   * //  'super' => 'genious'
   * // );
   *
   * str::parse('<xml><entries><cool>nice</cool></entries></xml>', 'xml');
   * // output: array(
   * //    'entries' => array(
   * //        'cool' => 'nice'
   * //    )
   * // );
   *
   * </code>
   *
   * @param  string  $string
   * @param  string  $mode
   * @return mixed
   */
  public static function parse($string, $mode = 'json') {

    if(is_array($string) || is_object($string)) return $string;

    switch($mode) {
      case 'json':
        return (array)@json_decode($string, true);
      case 'xml':
        return xml::parse($string);
      case 'url':
        return (array)@parse_url($string);
      case 'php':
        return @unserialize($string);
      case 'query':
        @parse_str($string, $result);
        return $result;
      default:
        return $string;
    }

  }

  /**
   * Encode a string (used for email addresses)
   *
   * @param  string  $string
   * @return string
   */
  public static function encode($string) {
    $string  = (string)$string;
    $encoded = '';
    for($i = 0; $i < static::length($string); $i++) {
      $char = static::substr($string, $i, 1);
      if(MB) {
        list(, $code) = unpack('N', mb_convert_encoding($char, 'UCS-4BE', 'UTF-8'));
      } else {
        $code = ord($char);
      }

      $encoded .= rand(1, 2) == 1 ? '&#' . $code . ';' : '&#x' . dechex($code) . ';';
    }
    return $encoded;
  }

  /**
   * Generates an "a mailto" tag
   *
   * <code>
   *
   * echo str::email('bastian@getkirby.com');
   * echo str::email('bastian@getkirby.com', 'mail me');
   *
   * </code>
   *
   * @param string $email The url for the a tag
   * @param mixed $text The optional text. If null, the url will be used as text
   * @param array $attr Additional attributes for the tag
   * @return string the generated html
   */
  public static function email($email, $text = false, $attr = array()) {
    return html::email($email, $text, $attr);
  }

  /**
   * Generates an a tag
   *
   * @param string $href The url for the a tag
   * @param mixed $text The optional text. If null, the url will be used as text
   * @param array $attr Additional attributes for the tag
   * @return string the generated html
   */
  public static function link($href, $text = null, $attr = array()) {
    return html::a($href, $text, $attr);
  }

  /**
   * Returns an array with all words in a string
   *
   * @param string $string
   */
  public static function words($string) {
    preg_match_all('/(\pL{4,})/iu', $string, $m);
    return array_shift($m);
  }

  /**
   * Returns an array with all sentences in a string
   *
   * @param string $string
   * @return string
   */
  public static function sentences($string) {
    return preg_split('/(?<=[.?!])\s+/', $string, -1, PREG_SPLIT_NO_EMPTY);
  }

  /**
   * Returns an array with all lines in a string
   *
   * @param string $string
   * @return array
   */
  public static function lines($string) {
    return str::split($string, PHP_EOL);
  }

  /**
   * Checks if the given string is a URL
   *
   * @param string $string
   * @return boolean
   */
  public static function isURL($string) {
    return filter_var($string, FILTER_VALIDATE_URL);
  }

  /**
   * Shortens a string and adds an ellipsis if the string is too long
   *
   * <code>
   *
   * echo str::short('This is a very, very, very long string', 10);
   * // output: This is a…
   *
   * echo str::short('This is a very, very, very long string', 10, '####');
   * // output: This i####
   *
   * </code>
   *
   * @param  string  $string The string to be shortened
   * @param  int     $length The final number of characters the string should have
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened string
   */
  public static function short($string, $length, $rep = '…') {
    if(!$length) return $string;
    if(static::length($string) <= $length) return $string;
    $string = static::substr($string, 0, $length);
    return $string . $rep;
  }

  /**
   * Creates an excerpt of a string
   * It removes all html tags first and then uses str::short
   *
   * @param  string  $string The string to be shortened
   * @param  int     $chars The final number of characters the string should have
   * @param  boolean $removehtml True: remove the HTML tags from the string first
   * @param  string  $rep The element, which should be added if the string is too long. Ellipsis is the default.
   * @return string  The shortened string
   */
  public static function excerpt($string, $chars = 140, $removehtml = true, $rep='…') {
    if($removehtml) $string = strip_tags($string);
    $string = str_replace(PHP_EOL, ' ', trim($string));
    if(static::length($string) <= $chars) return $string;
    return $chars == 0 ? $string : static::substr($string, 0, strrpos(static::substr($string, 0, $chars), ' ')) . $rep;
  }

  /**
   * The widont function makes sure that there are no
   * typographical widows at the end of a paragraph –
   * that's a single word in the last line
   *
   * @param string $string
   * @return string
   */
  public static function widont($string = '') {
    return preg_replace_callback('|([^\s])\s+([^\s]+)\s*$|u', function($matches) {
      if(str::contains($matches[2], '-')) {
        return $matches[1] . ' ' . str_replace('-', '&#8209;', $matches[2]);
      } else {
        return $matches[1] . '&nbsp;' . $matches[2];
      }
    }, $string);
  }

  /**
   * An UTF-8 safe version of substr()
   *
   * @param  string  $str
   * @param  int     $start
   * @param  int     $length
   * @return string
   */
  public static function substr($str, $start, $length = null) {
    $length = $length === null ? static::length($str) : $length;
    return MB ? mb_substr($str, $start, $length, 'UTF-8') : substr($str, $start, $length);
  }

  /**
   * An UTF-8 safe version of strtolower()
   *
   * @param  string  $str
   * @return string
   */
  public static function lower($str) {
    return MB ? mb_strtolower($str, 'UTF-8') : strtolower($str);
  }

  /**
   * An UTF-8 safe version of strotoupper()
   *
   * @param  string  $str
   * @return string
   */
  public static function upper($str) {
    return MB ? mb_strtoupper($str, 'UTF-8') : strtoupper($str);
  }

  /**
   * An UTF-8 safe version of strlen()
   *
   * @param  string  $str
   * @return string
   */
  public static function length($str) {
    return MB ? mb_strlen($str, 'UTF-8') : strlen($str);
  }

  /**
   * Checks if a str contains another string
   *
   * @param  string  $str
   * @param  string  $needle
   * @param  boolean $i ignore upper/lowercase
   * @return string
   */
  public static function contains($str, $needle, $i = true) {
    if($i) {
      $str    = static::lower($str);
      $needle = static::lower($needle);
    }
    return strstr($str, $needle) ? true : false;
  }

  /**
   * Generates a random string that may be used for cryptographic purposes
   *
   * WARNING (PHP < 7.0): This function does *not* produce secure random
   * strings and falls back to str::quickRandom with PHP < 7.0!
   *
   * @param int $length The length of the random string
   * @param string $type Pool type (type of allowed characters)
   * @return string
   */
  public static function random($length = false, $type = 'alphaNum') {
    // fall back to insecure str::quickRandom() on PHP < 7
    if(!function_exists('random_int') || !function_exists('random_bytes')) {
      return static::quickRandom($length, $type);
    }

    if(!$length) $length = random_int(5, 10);
    $pool = static::pool($type, false);

    // catch invalid pools
    if(!$pool) return false;

    // regex that matches all characters *not* in the pool of allowed characters
    $regex = '/[^' . $pool . ']/';

    // collect characters until we have our required length
    $result = '';
    while(($currentLength = strlen($result)) < $length) {
      $missing = $length - $currentLength;
      $bytes = random_bytes($missing);
      $result .= substr(preg_replace($regex, '', base64_encode($bytes)), 0, $missing);
    }

    return $result;
  }

  /**
   * Quickly generates a random string
   *
   * WARNING: Should not be considered sufficient for cryptography, etc.
   *
   * @param int $length The length of the random string
   * @param string $type Pool type (type of allowed characters)
   * @return string
   */
  public static function quickRandom($length = false, $type = 'alphaNum') {
    if(!$length) $length = rand(5, 10);
    $pool = static::pool($type, false);

    // catch invalid pools
    if(!$pool) return false;

    return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
  }

  /**
   * Convert a string to a safe version to be used in a URL
   *
   * @param  string  $string The unsafe string
   * @param  string  $separator To be used instead of space and other non-word characters.
   * @return string  The safe string
   */
  public static function slug($string, $separator = null, $allowed = null) {

    $separator = $separator !== null ? $separator : static::$defaults['slug']['separator'];
    $allowed   = $allowed   !== null ? $allowed   : static::$defaults['slug']['allowed'];

    $string = trim($string);
    $string = static::lower($string);
    $string = static::ascii($string);

    // replace spaces with simple dashes
    $string = preg_replace('![^' . $allowed . ']!i', $separator, $string);

    if(strlen($separator) > 0) {
      // remove double separators
      $string = preg_replace('![' . preg_quote($separator) . ']{2,}!', $separator, $string);
    }

    // trim trailing and leading dashes
    $string = trim($string, $separator);

    // replace slashes with dashes
    $string = str_replace('/', $separator, $string);

    return $string;

  }

  /**
   * Better alternative for explode()
   * It takes care of removing empty values
   * and it has a built-in way to skip values
   * which are too short.
   *
   * @param  string  $string The string to split
   * @param  string  $separator The string to split by
   * @param  int     $length The min length of values.
   * @return array   An array of found values
   */
  public static function split($string, $separator = ',', $length = 1) {

    if(is_array($string)) return $string;

    $string = trim($string, $separator);
    $parts  = explode($separator, $string);
    $out    = array();

    foreach($parts AS $p) {
      $p = trim($p);
      if(static::length($p) > 0 && static::length($p) >= $length) $out[] = $p;
    }

    return $out;

  }

  /**
   * An UTF-8 safe version of ucwords()
   *
   * @param  string  $string
   * @return string
   */
  public static function ucwords($string) {
    return MB ? mb_convert_case($string, MB_CASE_TITLE, 'UTF-8') : ucwords(strtolower($string));
  }

  /**
   * An UTF-8 safe version of ucfirst()
   *
   * @param  string $string
   * @return string
   */
  public static function ucfirst($string) {
    return static::upper(static::substr($string, 0, 1)) . static::lower(static::substr($string, 1));
  }

  /**
   * Tries to detect the string encoding
   *
   * @param string $string
   * @return string
   */
  public static function encoding($string) {

    if(MB) {
      return mb_detect_encoding($string, 'UTF-8, ISO-8859-1, windows-1251', true);
    } else {
      foreach(array('utf-8', 'iso-8859-1', 'windows-1251') as $item) {
        if(md5(iconv($item, $item, $string)) == md5($string)) return $item;
      }
      return false;
    }

  }

  /**
   * Converts a string to a different encoding
   *
   * @param string $string
   * @param string $targetEncoding
   * @param string $sourceEncoding (optional)
   * @return string
   */
  public static function convert($string, $targetEncoding, $sourceEncoding = null) {
    // detect the source encoding if not passed as third argument
    if(is_null($sourceEncoding)) $sourceEncoding = static::encoding($string);

    // no need to convert if the target encoding is the same
    if(strtolower($sourceEncoding) === strtolower($targetEncoding)) return $string;

    return iconv($sourceEncoding, $targetEncoding, $string);
  }

  /**
   * Converts a string to UTF-8
   *
   * @param  string  $string
   * @return string
   */
  public static function utf8($string) {
    return static::convert($string, 'utf-8');
  }

  /**
   * A better way to strip slashes
   *
   * @param  string  $string
   * @return string
   */
  public static function stripslashes($string) {
    if(is_array($string)) return $string;
    return get_magic_quotes_gpc() ? stripslashes($string) : $string;
  }

  /**
   * A super simple string template engine,
   * which replaces tags like {mytag} with any other string
   *
   * @param  string $string
   * @param  array  $data An associative array with keys, which should be replaced and values.
   * @return string
   */
  public static function template($string, $data = array()) {
    $replace = array();
    foreach($data as $key => $value) $replace['{' . $key . '}'] = $value;
    return str_replace(array_keys($replace), array_values($replace), $string);
  }

  /**
   * Convert a string to 7-bit ASCII.
   *
   * @param  string  $string
   * @return string
   */
  public static function ascii($string) {
    $foreign = static::$ascii;
    $string  = preg_replace(array_keys($foreign), array_values($foreign), $string);
    return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $string);
  }

  /**
   * Forces a download of the string as text file
   *
   * @param string $string
   * @param string $name Optional name for the downloaded file
   */
  public static function download($string, $name = null) {

    header::download(array(
      'name' => $name ? $name : 'text.txt',
      'size' => static::length($string),
      'mime' => 'text/plain',
    ));

    die($string);

  }

  /**
   * Checks if a string starts with the passed needle
   *
   * @param string $string
   * @param string $needle
   * @return boolean
   */
  public static function startsWith($string, $needle) {
    return $needle === '' || strpos($string, $needle) === 0;
  }

  /**
   * Checks if a string ends with the passed needle
   *
   * @param string $string
   * @param string $needle
   * @return boolean
   */
  public static function endsWith($string, $needle) {
    return $needle === '' || static::substr($string, -static::length($needle)) === $needle;
  }

  /**
   * Get a character pool with various possible combinations
   *
   * @param  string  $type
   * @param  boolean $array
   * @return string
   */
  public static function pool($type, $array = true) {

    $pool = array();

    if(is_array($type)) {
      foreach($type as $t) {
        $pool = array_merge($pool, static::pool($t));
      }
    } else {

      switch($type) {
        case 'alphaLower':
          $pool = range('a','z');
          break;
        case 'alphaUpper':
          $pool = range('A', 'Z');
          break;
        case 'alpha':
          $pool = static::pool(array('alphaLower', 'alphaUpper'));
          break;
        case 'num':
          $pool = range(0, 9);
          break;
        case 'alphaNum':
          $pool = static::pool(array('alpha', 'num'));
          break;
      }

    }

    return $array ? $pool : implode('', $pool);

  }

  /**
   * Returns the beginning of a string before the given character
   *
   * @param string $string
   * @param string $char
   * @return string
   */
  public static function before($string, $char) {
    $pos = strpos($string, $char);    
    if($pos !== false) {
      return static::substr($string, 0, $pos);      
    } else {
      return false;
    }
  }

  /**
   * Returns the beginning of a string until the given character
   *
   * @param string $string
   * @param string $char
   * @return string
   */
  public static function until($string, $char) {
    $pos = strpos($string, $char);
    if($pos !== false) {
      return static::substr($string, 0, $pos + str::length($char));      
    } else {
      return false;
    }
  }

  /**
   * Returns the rest of the string after the given character
   *
   * @param string $string
   * @param string $char
   * @return string
   */
  public static function after($string, $char) {
    $pos = strpos($string, $char);
    if($pos !== false) {
      return static::substr($string, $pos + str::length($char));      
    } else {
      return false;
    }
  }

  /**
   * Returns the rest of the string starting from the given character
   *
   * @param string $string
   * @param string $char
   * @return string
   */
  public static function from($string, $char) {
    $pos = strpos($string, $char);
    if($pos !== false) {
      return static::substr($string, $pos);      
    } else {
      return false;
    }
  }

  /**
   * Returns everything between two strings from the first occurrence of a given string
   * 
   * @param string $string
   * @param string $start
   * @param string $end
   * @return string
   */
  public static function between($string, $start, $end) {
    return static::before(static::after($string, $start), $end);
  }

  /**
   * Replaces all or some occurrences of the search string with the replacement string
   * Extension of the str_replace() function in PHP with an additional $limit parameter
   *
   * @param  string|array $string  String being replaced on (haystack);
   *                               can be an array of multiple subject strings
   * @param  string|array $search  Value being searched for (needle)
   * @param  string|array $replace Value to replace matches with
   * @param  int|array    $limit   Maximum possible replacements for each search value;
   *                               multiple limits for each search value are supported;
   *                               defaults to no limit
   * @return string|array          String with replaced values;
   *                               if $string is an array, array of strings
   */
  public static function replace($string, $search, $replace, $limit = -1) {
    // convert Kirby collections to arrays
    if(is_a($string,  'Collection')) $string  = $string->toArray();
    if(is_a($search,  'Collection')) $search  = $search->toArray();
    if(is_a($replace, 'Collection')) $replace = $replace->toArray();

    // without a limit we might as well use the built-in function
    if($limit === -1) return str_replace($search, $replace, $string);

    // if the limit is zero, the result will be no replacements at all
    if($limit === 0) return $string;

    // multiple subjects are run separately through this method
    if(is_array($string)) {
      $result = [];
      foreach($string as $s) {
        $result[] = static::replace($s, $search, $replace, $limit);
      }

      return $result;
    }

    // build an array of replacements
    // we don't use an associative array because otherwise you couldn't
    // replace the same string with different replacements
    $replacements = static::makeReplacements($search, $replace, $limit);

    // run the string and the replacement array through the replacer
    return static::replaceReplacements($string, $replacements);
  }

  /**
   * Generates a replacement array out of dynamic input data
   * Used for Str::replace()
   *
   * @param  string|array $search  Value being searched for (needle)
   * @param  string|array $replace Value to replace matches with
   * @param  int|array    $limit   Maximum possible replacements for each search value;
   *                               multiple limits for each search value are supported;
   *                               defaults to no limit
   * @return array                 List of replacement arrays, each with a
   *                               'search', 'replace' and 'limit' attribute
   */
  public static function makeReplacements($search, $replace, $limit) {
    $replacements = [];

    if(is_array($search) && is_array($replace)) {
      foreach($search as $i => $s) {
        // replace with an empty string if no replacement string was defined for this index;
        // behavior is identical to the official PHP str_replace()
        $r = (isset($replace[$i]))? $replace[$i] : '';

        if(is_array($limit)) {
          // don't apply a limit if no limit was defined for this index
          $l = (isset($limit[$i]))? $limit[$i] : -1;
        } else {
          $l = $limit;
        }

        $replacements[] = ['search' => $s, 'replace' => $r, 'limit' => $l];
      }
    } else if(is_array($search) && is_string($replace)) {
      foreach($search as $i => $s) {
        if(is_array($limit)) {
          // don't apply a limit if no limit was defined for this index
          $l = (isset($limit[$i]))? $limit[$i] : -1;
        } else {
          $l = $limit;
        }

        $replacements[] = ['search' => $s, 'replace' => $replace, 'limit' => $l];
      }
    } else if(is_string($search) && is_string($replace) && is_int($limit)) {
      $replacements[] = compact('search', 'replace', 'limit');
    } else {
      throw new Error('Invalid combination of $search, $replace and $limit params.');
    }

    return $replacements;
  }

  /**
   * Takes a replacement array and processes the replacements
   * Used for Str::replace()
   *
   * @param  string $string       String being replaced on (haystack)
   * @param  array  $replacements Replacement array from Str::makeReplacements()
   * @return string               String with replaced values
   */
  public static function replaceReplacements($string, $replacements) {
    // replace in the order of the replacements
    // behavior is identical to the official PHP str_replace()
    foreach($replacements as $r) {
      if(!is_int($r['limit'])) {
        throw new Error('Invalid limit "' . $r['limit'] . '".');
      } else if($r['limit'] === -1) {
        // no limit, we don't need our special replacement routine
        $string = str_replace($r['search'], $r['replace'], $string);
      } else if($r['limit'] > 0) {
        // limit given, only replace for $r['limit'] times per replacement
        $pos = -1;
        for($i = 0; $i < $r['limit']; $i++) {
          $pos = strpos($string, $r['search'], $pos + 1);
          if(is_int($pos)) {
            $string = substr_replace($string, $r['replace'], $pos, strlen($r['search']));

            // adapt $pos to the now changed offset
            $pos = $pos + strlen($r['replace']) - strlen($r['search']);
          } else {
            // no more match in the string
            break;
          }
        }
      }
    }

    return $string;
  }

}