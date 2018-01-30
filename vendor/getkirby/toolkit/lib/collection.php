<?php

/**
 * Collection
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Collection extends I implements Countable {

  public static $filters = array();

  protected $pagination;

  /**
   * Returns a slice of the collection
   *
   * @param int $offset The optional index to start the slice from
   * @param int $limit The optional number of elements to return
   * @return Collection
   */
  public function slice($offset = null, $limit = null) {
    if($offset === null && $limit === null) return $this;
    $collection = clone $this;
    $collection->data = array_slice($collection->data, $offset, $limit);
    return $collection;
  }

  /**
   * Returns a new combined collection
   *
   * @return Collection
   */

  public function merge($collection2) {
    $collection = clone $this;
    $collection->data = a::merge($collection->data, $collection2->data);
    return $collection;
  }

  /**
   * Returns a new collection with a limited number of elements
   *
   * @param int $limit The number of elements to return
   * @return Collection
   */
  public function limit($limit) {
    return $this->slice(0, $limit);
  }

  /**
   * Returns a new collection starting from the given offset
   *
   * @param int $offset The index to start from
   * @return Collection
   */
  public function offset($offset) {
    return $this->slice($offset);
  }

  /**
   * Returns the array in reverse order
   *
   * @return Collection
   */
  public function flip() {
    $collection = clone $this;
    $collection->data = array_reverse($collection->data, true);
    return $collection;
  }

  /**
   * Counts all elements in the array
   *
   * @return int
   */
  public function count() {
    return count($this->data);
  }

  /**
   * Returns the first element from the array
   *
   * @return mixed
   */
  public function first() {
    $array = $this->data;
    return array_shift($array);
  }

  /**
   * Checks if an element is in the collection by key. 
   * 
   * @param string $key
   * @return boolean
   */
  public function has($key) {
    return isset($this->data[$key]);
  }

  /**
   * Returns the last element from the array
   *
   * @return mixed
   */
  public function last() {
    $array = $this->data;
    return array_pop($array);
  }

  /**
   * Returns the nth element from the array
   *
   * @return mixed
   */
  public function nth($n) {
    $array = array_values($this->data);
    return (isset($array[$n])) ? $array[$n] : false;
  }

  /**
   * Converts the current object into an array
   *
   * @return array
   */
  public function toArray($callback = null) {
    if(is_null($callback)) return $this->data;
    return array_map($callback, $this->data);
  }

  /**
   * Converts the current object into a json string
   *
   * @return string
   */
  public function toJson() {
    return json_encode($this->data);
  }

  /**
   * Appends an element to the data array
   *
   * @param string $key
   * @param mixed $object
   * @return Collection
   */
  public function append($key, $object) {
    $this->data = $this->data + array($key => $object);
    return $this;
  }

  /**
   * Prepends an element to the data array
   *
   * @param string $key
   * @param mixed $object
   * @return Collection
   */
  public function prepend($key, $object) {
    $this->data = array($key => $object) + $this->data;
    return $this;
  }

  /**
   * Returns a new collection without the given element(s)
   *
   * @param args any number of keys, passed as individual arguments
   * @return Collection
   */
  public function not() {
    $collection = clone $this;
    foreach(func_get_args() as $kill) {
      unset($collection->data[$kill]);
    }
    return $collection;
  }

  /**
   * Returns a new collection without the given element(s)
   *
   * @param args any number of keys, passed as individual arguments
   * @return Collection
   */
  public function without() {
    return call_user_func_array(array($this, 'not'), func_get_args());
  }

  /**
   * Shuffle all elements in the array
   *
   * @return object a new shuffled collection
   */
  public function shuffle() {
    $keys = array_keys($this->data);
    shuffle($keys);
    
    $collection = clone $this;
    $collection->data = array();
    foreach($keys as $key) {
      $collection->data[$key] = $this->data[$key];
    }
    
    return $collection;
  }

  /**
   * Returns an array of all keys in the collection
   *
   * @return array
   */
  public function keys() {
    return array_keys($this->data);
  }

  /**
   * Tries to find the key for the given element
   *
   * @param  mixed $needle the element to search for
   * @return mixed the name of the key or false
   */
  public function keyOf($needle) {
    return array_search($needle, $this->data);
  }

  /**
   * Tries to find the index number for the given element
   *
   * @param  mixed $needle the element to search for
   * @return mixed the name of the key or false
   */
  public function indexOf($needle) {
    return array_search($needle, array_values($this->data));
  }

  /**
   * Filter the elements in the array by a callback function
   *
   * @param  func $callback the callback function
   * @return Collection
   */
  public function filter($callback) {
    $collection = clone $this;
    $collection->data = array_filter($collection->data, $callback);
    return $collection;
  }

  /**
   * Find a single item by a key and value pair
   *
   * @param string $key
   * @param mixed $value
   * @return mixed
   */
  public function findBy($key, $value) {
    foreach($this->data as $item) {
      if($this->extractValue($item, $key) == $value) return $item;
    }
  }

  /**
   * Filters the current collection by a field, operator and search value
   *
   * @return Collection
   */
  public function filterBy() {

    $args       = func_get_args();
    $operator   = '==';
    $field      = @$args[0];
    $value      = @$args[1];
    $split      = @$args[2];
    $collection = clone $this;

    if(is_string($value) && array_key_exists($value, static::$filters)) {
      $operator = $value;
      $value    = @$args[2];
      $split    = @$args[3];
    }

    if(is_object($value)) {
      $value = (string)$value;
    }

    if(array_key_exists($operator, static::$filters)) {

      $collection = call_user_func_array(static::$filters[$operator], array(
        $collection,
        $field,
        $value,
        $split
      ));

    }

    return $collection;

  }

  /**
   * Makes sure to provide a valid value for each filter method
   * no matter if an object or an array is given
   *
   * @param mixed $item
   * @param string $field
   * @return mixed
   */
  static public function extractValue($item, $field) {
    if(is_array($item) && isset($item[$field])) {
      return $item[$field];
    } else if(is_object($item)) {
      return $item->$field();
    } else {
      return false;
    }
  }

  /**
   * Sorts the collection by any number of fields
   *
   * @return  Collection
   */
  public function sortBy() {

    $args       = func_get_args();
    $collection = clone $this;
    $array      = $collection->data;

    // there is no need to sort empty collections
    if(empty($array)) return $collection;

    // loop through all method arguments and find sets of fields to sort by
    $fields = [];
    foreach($args as $i => $arg) {
      // get the index of the latest field array inside the $fields array
      $currentField = ($fields)? count($fields) - 1 : 0;

      // detect the type of argument
      // sorting direction
      $argLower = str::lower($arg);
      if($arg === SORT_ASC || $argLower === 'asc') {
        $fields[$currentField]['direction'] = SORT_ASC;
      } else if($arg === SORT_DESC || $argLower === 'desc') {
        $fields[$currentField]['direction'] = SORT_DESC;
      
      // other string: The field name
      } else if(is_string($arg)) {
        $values = $collection->pluck($arg);
        $fields[] = ['field' => $arg, 'values' => $values];
      
      // flags
      } else {
        $fields[$currentField]['flags'] = $arg;
      }
    }

    // build the multisort params
    $params = [];
    foreach($fields as $field) {
      $params[] = a::get($field, 'values',    []);
      $params[] = a::get($field, 'direction', SORT_ASC);
      $params[] = a::get($field, 'flags',     SORT_LOCALE_STRING);
    }
    $params[] = &$array;

    // array_multisort receives $params as separate params
    call('array_multisort', $params);

    // $array has been overwritten by array_multisort
    $collection->data = $array;
    return $collection;

  }

  /**
   * Add pagination
   *
   * @param int $limit the number of items per page
   * @param array $options and optional array with options for the pagination class
   * @return object a sliced set of data
   */
  public function paginate($limit, $options = array()) {

    if(is_a($limit, 'Pagination')) {
      $this->pagination = $limit;
      return $this;
    }

    $pagination = new Pagination($this->count(), $limit, $options);
    $pages = $this->slice($pagination->offset(), $pagination->limit());
    $pages->pagination = $pagination;

    return $pages;

  }

  /**
   * Get the previously added pagination object
   *
   * @return object
   */
  public function pagination() {
    return $this->pagination;
  }

  /**
   * Map a function to each item in the collection
   *
   * @param function $callback
   * @return Collection
   */
  public function map($callback) {
    $this->data = array_map($callback, $this->data);
    return $this;
  }

  /**
   * Extracts all values for a single field into
   * a new array
   *
   * @param string $field
   * @return array
   */
  public function pluck($field, $split = null, $unique = false) {

    $result = array();

    foreach($this->data as $item) {
      $row = $this->extractValue($item, $field);

      if($split) {
        $result = array_merge($result, str::split($row, $split));
      } else {
        $result[] = $row;
      }

    }

    if($unique) {
      $result = array_unique($result);
    }

    return array_values($result);

  }

  /**
   * Groups the collection by a given callback
   *
   * @param callable $callback
   * @return object A new collection with an item for each group and a subcollection in each group
   */
  public function group($callback) {

    if (!is_callable($callback)) throw new Exception($callback . ' is not callable. Did you mean to use groupBy()?');

    $groups = array();

    foreach($this->data as $key => $item) {

      // get the value to group by
      $value = call_user_func($callback, $item);

      // make sure that there's always a proper value to group by
      if(!$value) throw new Exception('Invalid grouping value for key: ' . $key);

      // make sure we have a proper key for each group
      if(is_array($value)) {
        throw new Exception('You cannot group by arrays or objects');
      } else if(is_object($value)) {
        if(!method_exists($value, '__toString')) {
          throw new Exception('You cannot group by arrays or objects');
        } else {
          $value = (string)$value;
        }
      }

      if(!isset($groups[$value])) {
        // create a new entry for the group if it does not exist yet
        $groups[$value] = new static(array($key => $item));
      } else {
        // add the item to an existing group
        $groups[$value]->set($key, $item);
      }

    }

    return new Collection($groups);

  }

  /**
   * Groups the collection by a given field
   *
   * @param string $field
   * @return object A new collection with an item for each group and a subcollection in each group
   */
  public function groupBy($field, $i = true) {

    if (!is_string($field)) throw new Exception('Cannot group by non-string values. Did you mean to call group()?');

    return $this->group(function($item) use ($field, $i) {

      $value = $this->extractValue($item, $field);

      // ignore upper/lowercase for group names
      return ($i == true) ? str::lower($value) : $value;

    });

  }

  /**
   * Creates chunks of the same size
   * The last chunk may be smaller
   *
   * @param int $size Number of items per chunk
   * @return object A new collection with an item for each chunk and a subcollection in each chunk
   */
  public function chunk($size) {

    // create a multidimensional array that is chunked with the given chunk size
    // keep keys of the items
    $chunks = array_chunk($this->data, $size, true);

    // convert each subcollection to a collection object
    $chunkCollections = array();
    foreach($chunks as $items) {
      // we clone $this instead of creating a new object because
      // different collections may have different constructors
      $collection = clone $this;
      $collection->data = $items;
      $chunkCollections[] = $collection;
    }

    // convert the array of chunks to a collection object
    return new Collection($chunkCollections);

  }

  public function set($key, $value) {
    if(is_array($key)) {
      $this->data = array_merge($this->data, $key);
      return $this;
    }
    $this->data[$key] = $value;
    return $this;
  }

  public function __set($key, $value) {
    $this->set($key, $value);
  }

  public function get($key, $default = null) {
    if(isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      $lowerkeys = array_change_key_case($this->data, CASE_LOWER);
      if(isset($lowerkeys[strtolower($key)])) {
        return $lowerkeys[strtolower($key)];
      } else {
        return $default;
      }
    }
  }

  public function __get($key) {
    return $this->get($key);
  }

  public function __call($key, $arguments) {
    return $this->get($key);
  }

  /**
   * Makes it possible to echo the entire object
   *
   * @return string
   */
  public function __toString() {
    return implode('<br />', array_map(function($item) {
      return (string)$item;
    }, $this->data));
  }

  /**
   * Improved var_dump() output
   * 
   * @return array
   */
  public function __debuginfo() {
    return $this->data;
  }

}


/**
 * Add all available collection filters
 * Those can be extended by creating your own:
 * collection::$filters['your operator'] = function($collection, $field, $value, $split = false) {
 *   // your filter code
 * };
 */

// take all matching elements
collection::$filters['=='] = function($collection, $field, $value, $split = false) {

  foreach($collection->data as $key => $item) {

    if($split) {
      $values = str::split((string)collection::extractValue($item, $field), $split);
      if(!in_array($value, $values)) unset($collection->data[$key]);
    } else if(collection::extractValue($item, $field) != $value) {
      unset($collection->data[$key]);
    }

  }

  return $collection;

};

// take all elements that match one element from the passed array
collection::$filters['in'] = function($collection, $field, $value, $split = false) {
  if(!is_array($value)) $value = [$value];

  foreach($collection->data as $key => $item) {

    if($split) {
      $values = str::split((string)collection::extractValue($item, $field), $split);

      $match = false;
      foreach($value as $v) {
        if(in_array($v, $values)) $match = true;
      }
      if(!$match) unset($collection->data[$key]);
    } else if(!in_array(collection::extractValue($item, $field), $value)) {
      unset($collection->data[$key]);
    }

  }

  return $collection;

};

// take all elements that won't match
collection::$filters['!='] = function($collection, $field, $value, $split = false) {

  foreach($collection->data as $key => $item) {
    if($split) {
      $values = str::split((string)collection::extractValue($item, $field), $split);
      if(in_array($value, $values)) unset($collection->data[$key]);
    } else if(collection::extractValue($item, $field) == $value) {
      unset($collection->data[$key]);
    }
  }

  return $collection;

};

// take all elements that don't match an element from the passed array
collection::$filters['not in'] = function($collection, $field, $value, $split = false) {
  if(!is_array($value)) $value = [$value];

  foreach($collection->data as $key => $item) {

    if($split) {
      $values = str::split((string)collection::extractValue($item, $field), $split);

      foreach($value as $v) {
        if(in_array($v, $values)) {
          unset($collection->data[$key]);
          break;
        }
      }
    } else if(in_array(collection::extractValue($item, $field), $value)) {
      unset($collection->data[$key]);
    }

  }

  return $collection;

};

// take all elements that partly match
collection::$filters['*='] = function($collection, $field, $value, $split = false) {

  foreach($collection->data as $key => $item) {
    if($split) {
      $values = str::split((string)collection::extractValue($item, $field), $split);
      foreach($values as $val) {
        if(strpos($val, $value) === false) {
          unset($collection->data[$key]);
          break;
        }
      }
    } else if(strpos(collection::extractValue($item, $field), $value) === false) {
      unset($collection->data[$key]);
    }
  }

  return $collection;

};

// greater than
collection::$filters['>'] = function($collection, $field, $value) {

  foreach($collection->data as $key => $item) {
    if(collection::extractValue($item, $field) > $value) continue;
    unset($collection->data[$key]);
  }

  return $collection;

};

// greater and equals
collection::$filters['>='] = function($collection, $field, $value) {

  foreach($collection->data as $key => $item) {
    if(collection::extractValue($item, $field) >= $value) continue;
    unset($collection->data[$key]);
  }

  return $collection;

};

// less than
collection::$filters['<'] = function($collection, $field, $value) {

  foreach($collection->data as $key => $item) {
    if(collection::extractValue($item, $field) < $value) continue;
    unset($collection->data[$key]);
  }

  return $collection;

};

// less and equals
collection::$filters['<='] = function($collection, $field, $value) {

  foreach($collection->data as $key => $item) {
    if(collection::extractValue($item, $field) <= $value) continue;
    unset($collection->data[$key]);
  }

  return $collection;

};
