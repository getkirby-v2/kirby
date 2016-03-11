<?php

/**
 * Field
 */
class Field extends FieldAbstract {

  /**
   * Returns if a field is translated in the current/provided language
   * @param  string  $lang Language code
   * @return boolean
   */
  public function isTranslated($lang = null) {

    if(is_null($lang)) $lang = $this->site->language->code;

    $current = $this->page->content($lang);
    $default = $this->page->content($this->site->defaultLanguage->code);

    $field        = $current->get($this->key);
    $untranslated = $default->get($this->key)->value();

    return $field->isNotEmpty() and $field->value() !== $untranslated;

  }


}
