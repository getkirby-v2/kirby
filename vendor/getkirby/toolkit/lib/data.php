<?php

/**
 * Data
 *
 * Universal data writer/reader/decoder/encoder for
 * json, yaml and structured kirby content
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Data {

  const ERROR_INVALID_ADAPTER = 0;

  public static $adapters = array();

  public static function adapter($type) {

    if(isset(static::$adapters[$type])) return static::$adapters[$type];

    foreach(static::$adapters as $adapter) {
      if(is_array($adapter['extension']) && in_array($type, $adapter['extension'])) {
        return $adapter;
      } else if($adapter['extension'] == $type) {
        return $adapter;
      }
    }

    throw new Error('Invalid adapter type', static::ERROR_INVALID_ADAPTER);

  }

  public static function encode($data, $type) {
    $adapter = static::adapter($type);
    return call_user_func($adapter['encode'], $data);
  }

  public static function decode($data, $type) {
    $adapter = static::adapter($type);
    return call_user_func($adapter['decode'], $data);
  }

  public static function read($file, $type = null) {

    // type autodetection
    if(is_null($type)) $type = f::extension($file);

    // get the adapter
    $adapter = static::adapter($type);

    if(isset($adapter['read'])) {
      return call($adapter['read'], $file);
    } else {
      return data::decode(f::read($file), $type);
    }


  }

  public static function write($file, $data, $type = null) {
    // type autodetection
    if(is_null($type)) $type = f::extension($file);
    return f::write($file, data::encode($data, $type));
  }

}


/**
 * Json adapter
 */
data::$adapters['json'] = array(
  'extension' => 'json',
  'encode' => function($data) {
    return json_encode($data);
  },
  'decode' => function($string) {
    return json_decode($string, true);
  }
);


/**
 * Kirby data adapter
 */
data::$adapters['kd'] = array(
  'extension' => array('md', 'txt'),
  'encode' => function($data) {

    $result = array();
    foreach($data AS $key => $value) {
      $key = str::ucfirst(str::slug($key));

      if(empty($key) || is_null($value)) continue;

      // avoid problems with arrays
      if(is_array($value)) {
        $value = '';
      }

      // escape accidental dividers within a field
      $value = preg_replace('!(\n|^)----(.*?\R*)!', "$1\\----$2", $value);

      // multi-line content
      if(preg_match('!\R!', $value, $matches)) {
        $result[$key] = $key . ": \n\n" . trim($value);
      // single-line content
      } else {
        $result[$key] = $key . ': ' . trim($value);        
      }

    }
    return implode("\n\n----\n\n", $result);

  },
  'decode' => function($string) {

    // remove BOM
    $string = str_replace(BOM, '', $string);
    // explode all fields by the line separator
    $fields = preg_split('!\n----\s*\n*!', $string);
    // start the data array
    $data   = array();

    // loop through all fields and add them to the content
    foreach($fields as $field) {
      $pos = strpos($field, ':');
      $key = str_replace(array('-', ' '), '_', strtolower(trim(substr($field, 0, $pos))));

      // Don't add fields with empty keys
      if(empty($key)) continue;
      $data[$key] = trim(substr($field, $pos+1));

    }

    return $data;

  }
);


/**
 * PHP serializer adapter
 */
data::$adapters['php'] = array(
  'extension' => array('php'),
  'encode' => function($array) {
    return '<?php ' . PHP_EOL . PHP_EOL . 'return ' . var_export($array, true) . PHP_EOL . PHP_EOL . '?>';
  },
  'decode' => function() {
    throw new Error('Decoding PHP strings is not supported');
  },
  'read' => function($file) {
    $array = require $file;
    return $array;
  }
);


/**
 * YAML adapter
 */
data::$adapters['yaml'] = array(
  'extension' => array('yaml', 'yml'),
  'encode' => function($data) {
    return yaml::encode($data);
  },
  'decode' => function($string) {
    return yaml::decode($string);
  }
);