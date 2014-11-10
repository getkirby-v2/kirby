<?php

field::$methods['html'] = field::$methods['h'] = function($field) {
  $field->value = html($field->value);
  return $field;
};

field::$methods['escape'] = field::$methods['esc'] = function($field, $context = 'html') {
  $field->value = esc($field->value, $context);
  return $field;
};

field::$methods['xml'] = field::$methods['x'] = function($field) {
  $field->value = xml($field->value);
  return $field;
};

field::$methods['kirbytext'] = field::$methods['kt'] = function($field) {
  $field->value = kirbytext($field);
  return $field;
};

field::$methods['markdown'] = field::$methods['md'] = function($field) {
  $field->value = markdown($field->value);
  return $field;
};

field::$methods['lower'] = function($field) {
  $field->value = str::lower($field->value);
  return $field;
};

field::$methods['upper'] = function($field) {
  $field->value = str::upper($field->value);
  return $field;
};

field::$methods['widont'] = function($field) {
  $field->value = widont($field->value);
  return $field;
};

field::$methods['excerpt'] = function($field, $chars = 140) {
  return excerpt($field->value, $chars);
};

field::$methods['short'] = function($field, $length, $rep = '…') {
  return str::short($field->value, $length, $rep);
};

field::$methods['length'] = function($field) {
  return str::length($field->value);
};

field::$methods['words'] = function($field) {
  return str_word_count(strip_tags($field->value));
};

field::$methods['split'] = function($field, $separator = ',') {
  return str::split($field->value, $separator);
};

field::$methods['yaml'] = function($field) {
  return yaml($field->value);
};

field::$methods['empty'] = field::$methods['isEmpty'] = function($field) {
  return empty($field->value);
};

field::$methods['pages'] = function($field) {

  $values = $field->yaml();
  $related = array();

  // Handle structure fields
  if (is_array(a::first($values))) {
    $values = a::extract($values, 'uri');
  }

  foreach($values as $uri) {
    // Only add existing pages
    if($rel = page($uri)) $related[$rel->id()] = $rel;
  }

  return new Collection($related);

};
