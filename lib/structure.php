<?php 

class Structure extends Collection {

  public $page = null;

  public function get($key, $default = null) {

    if(isset($this->data[$key])) {
      return $this->data[$key];
    } else {
      $lowerkeys = array_change_key_case($this->data, CASE_LOWER);
      $lowerkey  = strtolower($key);
      if(isset($lowerkeys[$lowerkey])) {
        return $lowerkeys[$lowerkey];
      }
    }

    return new Field($this->page, $key, null);

  }

  /**
   * Get formatted date fields
   *
   * @param string $format
   * @param string $field
   * @return string
   */
  public function date($format = null, $field = 'date') {

    if($timestamp = strtotime($this->get($field))) {
      if(is_null($format)) {
        return $timestamp;
      } else {
        return kirby()->options['date.handler']($format, $timestamp);
      }
    }

  }

}