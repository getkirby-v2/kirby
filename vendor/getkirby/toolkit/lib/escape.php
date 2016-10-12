<?php

/**
 * Escape
 * 
 * Class to handle context specific output escaping per OWASP recommendations.
 * 
 * Most of this class is based on methods from Zend\Escaper, but modified for Kirby.
 * Copyrighted (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * under the New BSD License (http://framework.zend.com/license/new-bsd).
 * 
 * @link https://github.com/zendframework/zf2/blob/master/library/Zend/Escaper/Escaper.php
 * @link https://www.owasp.org/index.php/XSS_(Cross_Site_Scripting)_Prevention_Cheat_Sheet
 * 
 * @package   Kirby Toolkit
 * @author    Ezra Verheijen <ezra.verheijen@gmail.com>
 * @link      https://github.com/ezraverheijen/escape
 * @copyright Ezra Verheijen
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Escape {
  
  /**
   * Check if a string needs to be escaped or not
   * 
   * @param  string  $string
   * @return boolean
   */
  public static function noNeedToEscape($string) {
    return $string === '' || ctype_digit($string);
  }
  
  /**
   * Convert a character from UTF-8 to UTF-16BE
   * 
   * @param  string $char
   * @return string
   */
  public static function convertEncoding($char) {
    return str::convert($char, 'UTF-16BE', 'UTF-8');
  }
  
  /**
   * Check if a character is undefined in HTML
   * 
   * @param  string $char
   * @return boolean
   */
  public static function charIsUndefined($char) {
    $ascii = ord($char);
    return ($ascii <= 0x1f && $char != "\t" && $char != "\n" && $char != "\r")
      || ($ascii >= 0x7f && $ascii <= 0x9f);
  }
  
  /**
   * Escape HTML element content
   * 
   * This can be used to put untrusted data directly into the HTML body somewhere.
   * This includes inside normal tags like div, p, b, td, etc.
   * 
   * Escapes &, <, >, ", and ' with HTML entity encoding to prevent switching
   * into any execution context, such as script, style, or event handlers.
   * 
   * <body>...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...</body>
   * <div>...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...</div>
   * 
   * @param  string $string
   * @return string
   */
  public static function html($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
  }
  
  /**
   * Escape XML element content
   * 
   * Removes offending characters that could be wrongfully interpreted as XML markup.
   * 
   * The following characters are reserved in XML and will be replaced with their
   * corresponding XML entities:
   * 
   * ' is replaced with &apos;
   * " is replaced with &quot;
   * & is replaced with &amp;
   * < is replaced with &lt;
   * > is replaced with &gt;
   * 
   * @param  string $string
   * @return string 
   */
  public static function xml($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
  }
  
  /**
   * Escape common HTML attributes data
   * 
   * This can be used to put untrusted data into typical attribute values
   * like width, name, value, etc.
   * 
   * This should not be used for complex attributes like href, src, style,
   * or any of the event handlers like onmouseover.
   * Use esc($string, 'js') for event handler attributes, esc($string, 'url')
   * for src attributes and esc($string, 'css') for style attributes.
   * 
   * <div attr=...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...>content</div>
   * <div attr='...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...'>content</div>
   * <div attr="...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...">content</div>
   * 
   * @param  string $string
   * @param  string $strict Whether to escape characters like [space] % * + , - / ; < = > ^ and |
   *                        which is necessary in case of unquoted HTML attributes.
   * @return string
   */
  public static function attr($string, $strict = true) {
    if(static::noNeedToEscape($string)) return $string;
    if($strict === true) {
      return preg_replace_callback('/[^a-z0-9,\.\-_]/iSu', 'static::escapeAttrChar', $string);
    }
    return static::html($string);
  }
  
  /**
   * Escape JavaScript data values
   * 
   * This can be used to put dynamically generated JavaScript code
   * into both script blocks and event-handler attributes.
   * 
   * <script>alert('...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...')</script>
   * <script>x='...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...'</script>
   * <div onmouseover="x='...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...'"</div>
   * 
   * @param  string $string
   * @return string
   */
  public static function js($string) {
    if(static::noNeedToEscape($string)) return $string;
    return preg_replace_callback('/[^a-z0-9,\._]/iSu', 'static::escapeJSChar', $string);
  }
  
  /**
   * Escape HTML style property values
   * 
   * This can be used to put untrusted data into a stylesheet or a style tag.
   * 
   * Stay away from putting untrusted data into complex properties like url,
   * behavior, and custom (-moz-binding). You should also not put untrusted data
   * into IE’s expression property value which allows JavaScript.
   * 
   * <style>selector { property : ...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...; } </style>
   * <style>selector { property : "...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE..."; } </style>
   * <span style="property : ...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...">text</span>
   * 
   * @param  string $string
   * @return string
   */
  public static function css($string) {
    if(static::noNeedToEscape($string)) return $string;
    return preg_replace_callback('/[^a-z0-9]/iSu', 'static::escapeCSSChar', $string);
  }
  
  /**
   * Escape URL parameter values
   * 
   * This can be used to put untrusted data into HTTP GET parameter values.
   * This should not be used to escape an entire URI.
   * 
   * <a href="http://www.somesite.com?test=...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...">link</a>
   * 
   * @param string  $string
   * @return string
   */
  public static function url($string) {
    return rawurlencode($string);
  }
  
  /**
   * Escape character for HTML attribute
   * 
   * Callback function for preg_replace_callback() that applies HTML attribute
   * escaping to all matches.
   * 
   * @param  array $matches
   * @return mixed Unicode replacement if character is undefined in HTML,
   *               named HTML entity if available (only those that XML supports),
   *               upper hex entity if a named entity does not exist or
   *               entity with the &#xHH; format if ASCII value is less than 256.
   */
  protected static function escapeAttrChar($matches) {
    $char = $matches[0];
    
    if(static::charIsUndefined($char)) {
      return '&#xFFFD;';
    }
    
    $dec = hexdec(bin2hex($char));
    
    $namedEntities = array(
      34 => '&quot;', // "
      38 => '&amp;',  // &
      60 => '&lt;',   // <
      62 => '&gt;'    // >
    );
    
    if(isset($namedEntities[$dec])) {
      return $namedEntities[$dec];
    }
    
    if($dec > 255) {
      return sprintf('&#x%04X;', $dec);
    }
    
    return sprintf('&#x%02X;', $dec);
  }
  
  /**
   * Escape character for JavaScript
   * 
   * Callback function for preg_replace_callback() that applies Javascript
   * escaping to all matches.
   * 
   * @param  array  $matches
   * @return string
   */
  protected static function escapeJSChar($matches) {
    $char = $matches[0];
    if(str::length($char) == 1) {
      return sprintf('\\x%02X', ord($char));
    }
    $char = static::convertEncoding($char);
    return sprintf('\\u%04s', str::upper(bin2hex($char)));
  }
  
  /**
   * Escape character for CSS
   * 
   * Callback function for preg_replace_callback() that applies CSS
   * escaping to all matches.
   * 
   * @param  array  $matches
   * @return string
   */
  protected static function escapeCSSChar($matches) {
    $char = $matches[0];
    if(str::length($char) == 1) {
      $ord = ord($char);
    } else {
      $char = static::convertEncoding($char);
      $ord  = hexdec(bin2hex($char));
    }
    return sprintf('\\%X ', $ord);
  }
  
}
