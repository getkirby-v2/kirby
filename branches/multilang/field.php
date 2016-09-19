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

    $site = $this->page->site();

    // use current language if $lang not set
    if(is_null($lang)) $lang = $site->language()->code();

    // if language is default/fallback language
    if($site->language($lang)->default()) return true;

    $current = $this->page->content($lang);
    $default = $this->page->content($site->defaultLanguage->code);

    $field        = $current->get($this->key);
    $untranslated = $default->get($this->key)->value();

    return $field->isNotEmpty() && $field->value() !== $untranslated;

  }


}
