<?php

/**
 *
 * Language
 *
 * Some handy methods to handle multi-language support
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class L extends Silo {

  public static $data   = array();
  public static $locale = 'en_US';

  public static function get($key = null, $data = null, $locale = null) {

    // get all keys
    if($key === null) {
      return parent::get();

    // old default value behavior
    } else if(!is_array($data)) {
      return parent::get($key, $data);

    // MessageFormatter
    } else if(class_exists('MessageFormatter')) {
      if($locale === null) $locale = static::$locale;
      return MessageFormatter::formatMessage($locale, parent::get($key), $data);
    } else {
      throw new Exception('The MessageFormatter extension is missing, which is required to use string replacements in l::get');
    }

  }

}