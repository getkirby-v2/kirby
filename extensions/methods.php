<?php

/**
 * Converts the field value to valid html
 * @param Field $field The calling Kirby Field instance
 * @param boolean $keepTags Don't touch valid html tags 
 * @return Field
 */
field::$methods['html'] = field::$methods['h'] = function($field, $keepTags = true) {
  $field->value = html($field->value, $keepTags);
  return $field;
};

/**
 * Escapes unwanted characters in the field value
 * to protect from possible xss attacks or other
 * unwanted side effects in your html code
 * @param Field $field The calling Kirby Field instance
 * @param string $context html|attr|css|js|url
 * @return Field
 */
field::$methods['escape'] = field::$methods['esc'] = function($field, $context = 'html') {
  $field->value = esc($field->value, $context);
  return $field;
};

/**
 * Converts html entities and specialchars in the field
 * value to valid xml entities
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['xml'] = field::$methods['x'] = function($field) {
  $field->value = xml($field->value);
  return $field;
};

/**
 * Parses the field value as kirbytext
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['kirbytext'] = field::$methods['kt'] = function($field) {
  $field->value = kirbytext($field);
  return $field;
};

/**
 * Parses the field value as markdown
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['markdown'] = field::$methods['md'] = function($field) {
  $field->value = markdown($field->value);
  return $field;
};

/**
 * Parses the field value with SmartyPants
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['smartypants'] = field::$methods['sp'] = function($field) {
  $field->value = smartypants($field->value);
  return $field;
};

/**
 * Converts the field value to lower case
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['lower'] = function($field) {
  $field->value = str::lower($field->value);
  return $field;
};

/**
 * Converts the field value to upper case
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['upper'] = function($field) {
  $field->value = str::upper($field->value);
  return $field;
};

/**
 * Applies the widont rule to avoid single 
 * words on the last line
 * @param Field $field The calling Kirby Field instance
 * @return Field
 */
field::$methods['widont'] = function($field) {
  $field->value = widont($field->value);
  return $field;
};

/**
 * Creates a simple text excerpt without formats
 * @param Field $field The calling Kirby Field instance
 * @param integer $chars The desired excerpt length
 * @return string
 */
field::$methods['excerpt'] = function($field, $chars = 140, $mode = 'chars') {
  return excerpt($field, $chars, $mode);
};

/**
 * Shortens the field value by the given length
 * @param Field $field The calling Kirby Field instance
 * @param integer $length The desired string length
 * @param string $rep The attached ellipsis character if the string is longer
 * @return string
 */
field::$methods['short'] = function($field, $length, $rep = '…') {
  return str::short($field->value, $length, $rep);
};

/**
 * Returns the string length of the field value
 * @param Field $field The calling Kirby Field instance
 * @return integer
 */
field::$methods['length'] = function($field) {
  return str::length($field->value);
};

/**
 * Returns the word count for the field value
 * @param Field $field The calling Kirby Field instance
 * @return integer
 */
field::$methods['words'] = function($field) {
  return str_word_count(strip_tags($field->value));
};

/**
 * Splits the field value by the given separator
 * @param Field $field The calling Kirby Field instance
 * @param string $separator The string to split the field value by
 * @return array
 */
field::$methods['split'] = function($field, $separator = ',') {
  return str::split($field->value, $separator);
};

/**
 * Parses the field value as yaml and returns an array
 * @param Field $field The calling Kirby Field instance
 * @return array
 */
field::$methods['yaml'] = function($field) {
  return yaml($field->value);
};

/**
 * Checks if the field value is empty
 * @param Field $field The calling Kirby Field instance
 * @return boolean
 */
field::$methods['empty'] = field::$methods['isEmpty'] = function($field) {
  return empty($field->value);
};

/**
 * Checks if the field value is not empty
 * @param Field $field The calling Kirby Field instance
 * @return boolean
 */
field::$methods['isNotEmpty'] = function($field) {
  return !$field->isEmpty();
};

/**
 * Returns a page object from a uri in a field
 * @param Field $field The calling Kirby Field instance
 * @return Collection
 */
field::$methods['toPage'] = function($field) {
  return page($field->value);
};

/**
 * Returns all page objects from a yaml list or a $sep separated string in a field
 * @param Field $field The calling Kirby Field instance
 * @return Collection
 */
field::$methods['pages'] = field::$methods['toPages'] = function($field, $sep = null) {

  if($sep !== null) {
    $array = $field->split($sep);
  } else {
    $array = $field->yaml();
  }

  return pages($array);

};

/**
 * Returns a file object from a filename in a field
 * @param Field $field The calling Kirby Field instance
 * @return Collection
 */
field::$methods['toFile'] = function($field) {
  return $field->page()->file($field->value);
};

/**
 * Adds 'or' method to Field objects, which allows getting a field
 * value or getting back a default value if the field is empty.
 * @author fvsch <florent@fvsch.com>
 * @param Field $field The calling Kirby Field instance
 * @param mixed $fallback Fallback value returned if field is empty
 * @return mixed
 */
field::$methods['or'] = function($field, $fallback = null) {
  return $field->empty() ? $fallback : $field;
};

/**
 * Filter the Field value, or a fallback value if the Field is empty,
 * to get a boolean value. '1', 'on', 'true' or 'yes' will be true,
 * and everything else will be false.
 * @author fvsch <florent@fvsch.com>
 * @param Field $field The calling Kirby Field instance
 * @param boolean $default Default value returned if field is empty
 * @return boolean
 */
field::$methods['bool'] = field::$methods['isTrue'] = function($field, $default = false) {
  $val = $field->empty() ? $default : $field->value;
  return filter_var($val, FILTER_VALIDATE_BOOLEAN);
};

/**
 * Checks if the field content is false
 * @param Field $field The calling Kirby Field instance 
 * @return boolean
 */
field::$methods['isFalse'] = function($field) {
  return !$field->bool();
};

/**
 * Get an integer value for the Field.
 * @author fvsch <florent@fvsch.com>
 * @param Object(Field) [$field] The calling Kirby Field instance
 * @param integer [$default] Default value returned if field is empty
 * @return integer
 */
field::$methods['int'] = function($field, $default = 0) {
  $val = $field->empty() ? $default : $field->value;
  return intval($val);
}; 

/**
 * Get a float value for the Field
 * @param Field $field The calling Kirby Field instance
 * @param int $default Default value returned if field is empty
 * @return float
 */
field::$methods['float'] = function($field, $default = 0) {
  $val = $field->empty() ? $default : $field->value;
  return floatval($val);
};

field::$methods['toStructure'] = field::$methods['structure'] = function($field) {
  return structure($field->yaml(), $field->page());
};

field::$methods['link'] = function($field, $attr1 = array(), $attr2 = array()) {
  $a = new Brick('a', $field->value());
    
  if(is_string($attr1)) {
    $a->attr('href', url($attr1));
    $a->attr($attr2);    
  } else {
    $a->attr('href', $field->page()->url());
    $a->attr($attr1);    
  }

  return $a;

};

field::$methods['toUrl'] = field::$methods['url'] = function($field) {
  return url($field->value());
};
