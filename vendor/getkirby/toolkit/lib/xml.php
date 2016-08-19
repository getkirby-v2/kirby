<?php

/**
 * 
 * XML
 * 
 * The Kirby XML parser and creator Class
 * 
 * @package   Kirby Toolkit 
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Xml {

  /**
   * Converts a string to a xml-safe string
   * Converts it to html-safe first and then it
   * will replace html entities to xml entities
   *
   * <code>
   *
   * echo xml::encode('some über crazy stuff');
   * // output: some &#252;ber crazy stuff 
   *  
   * </code>
   *    
   * @param  string  $string
   * @param  boolean $html True: convert to html first
   * @return string
   */  
  public static function encode($string, $html = true) {

    // convert raw text to html safe text
    if($html) {
      $string = html::encode($string, false);
    }

    // convert html entities to xml entities
    return strtr($string, html::entities());

  }

  /**
   * Removes all xml entities from a string
   * and convert them to html entities first
   * and remove all html entities afterwards.
   *
   * <code>
   * 
   * echo xml::decode('some <em>&#252;ber</em> crazy stuff');
   * // output: some &uuml;ber crazy stuff
   * 
   * </code>
   * 
   * @param  string  $string
   * @return string
   */  
  public static function decode($string) {
    // convert xml entities to html entities
    $string = strtr($string, static::entities());
    return html::decode($string);
  }  

  /** 
   * Parses a XML string and returns an array
   * 
   * @param  string  $xml
   * @return mixed
   */
  public static function parse($xml) {

    $xml = preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $xml);
    $xml = @simplexml_load_string($xml, null, LIBXML_NOENT | LIBXML_NOCDATA);
    $xml = @json_encode($xml);
    $xml = @json_decode($xml, true);
    return (is_array($xml)) ? $xml : false;

  }

  /**
   * Returns a translation table of xml entities to html entities
   * 
   * @return array
   */
  public static function entities() {
    return array_flip(html::entities());    
  }

  /**
   * Creates an XML string from an array
   * 
   * @param  array   $array   The source array
   * @param  string  $tag     The name of the root element
   * @param  boolean $head    Include the xml declaration head or not
   * @param  string  $charset The charset, which should be used for the header
   * @param  int     $level   The indendation level
   * @return string  The XML string
   */
  public static function create($array, $tag = 'root', $head = true, $charset = 'utf-8', $tab = '  ', $level = 0) {
    $result  = ($level == 0 && $head) ? '<?xml version="1.0" encoding="' . $charset . '"?>' . PHP_EOL : '';
    $nlevel  = ($level + 1);
    $attr = '@attributes';
    $attributes = html::attr(a::get($array, $attr));
    if(count($array) == 1 and $attributes) {
      // return the self closed node
      return str_repeat($tab, $level) . '<' . $tag . ($attributes ? ' ' . $attributes : '') . ' />' . PHP_EOL;
    } else {
      $result .= str_repeat($tab, $level) . '<' . $tag . ($attributes ? ' ' . $attributes . ' ' : '') . '>' . PHP_EOL;
    }
    foreach($array as $key => $value) {
      $key = str::lower($key);
      if($key == $attr) {
        continue;
      }
      if(is_array($value)) {
        $mtags = false;
        foreach($value as $key2 => $value2) {
          if($key2 == $attr) {
            continue;
          }
          if(is_array($value2)) {
            $result .= static::create($value2, $key2, $head, $charset, $tab, $nlevel);
          } elseif(!is_numeric($key)) {
            $result .= static::create($value, $key, $head, $charset, $tab, $nlevel);
          } elseif(trim($value2) != '') {
            $value2  = (!strstr($value2, '<![CDATA[') and htmlspecialchars($value2) != $value2) ? '<![CDATA[' . $value2 . ']]>' : $value2;
            $result .= str_repeat($tab, $nlevel) . '<' . $key2 . '>' . $value2 . '</' . $key2 . '>' . PHP_EOL;
          }
          $mtags = true;
        }
        if(!$mtags && count($value) > 0) {
          $result .= static::create($value, $key, $head, $charset, $tab, $nlevel);
        }
      } elseif(trim($value) != '') {
        $value   = (!strstr($value, '<![CDATA[') and htmlspecialchars($value) != $value) ? '<![CDATA[' . $value . ']]>' : $value;
        $result .= str_repeat($tab, $nlevel) . (is_numeric($key) ? '' : '<' . $key . '>') . $value . (is_numeric($key) ? '' : '</' . $key . '>') . PHP_EOL;
      }
    }
    return $result . str_repeat($tab, $level) . '</' . $tag . '>' . PHP_EOL;
  }

}
